<?php

use Carbon\Carbon;
use Faker\Factory;
use Dvlpp\Metrics\Metric;
use Dvlpp\Metrics\Visit;
use Dvlpp\Metrics\TimeInterval;
use Dvlpp\Metrics\Repositories\VisitRepository;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Session\Middleware\StartSession; 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Dvlpp\Metrics\Repositories\Eloquent\VisitModel;
use Dvlpp\Metrics\Contracts\AnalyzerInterface;
use Dvlpp\Metrics\Compiler;

abstract class MetricTestCase extends Illuminate\Foundation\Testing\TestCase
{
    use InteractsWithDatabase;

    protected $faker;

    public function __construct()
    {
        $this->faker = Faker\Factory::create();
    }

    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('app.debug', true);  
        $this->app['config']->set('mail.driver', 'log'); 
        $this->app['config']->set('session.driver', 'array'); 
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');
        $this->app['config']->set('metrics.logging', true);
        $this->migrateDatabase();
        $this->addLoginRoute();
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $app->register(\Dvlpp\Metrics\MetricServiceProvider::class);
       
        return $app;
    }
   
    /**
     * Run Migrations
     * 
     * @return void
     */
    protected function migrateDatabase()
    {
        $migrationPaths = [
            __DIR__ . "/../src/database/migrations",
            __DIR__ . "/../vendor/laravel/laravel/database/migrations",
        ];

        foreach($migrationPaths as $path) {
            $this->migrateDatabaseFromPath($path);
        }
    }

    /**
     * Run all database migrations from the specified path
     * 
     * @param  string $path
     * @return void
     */
    protected function migrateDatabaseFromPath($path)
    {
        $fileSystem = new Filesystem;
        $classFinder = new ClassFinder;

        foreach ($fileSystem->files($path) as $file) {
            
            $fileSystem->requireOnce($file);
            $migrationClass = $classFinder->findClass($file);

            (new $migrationClass)->up();
        }
    }

    /**
     * Add a login route for testing purpose
     *
     * @return void
     */
    protected function addLoginRoute()
    {
        $router = $this->app->make('router');
        $router->post('auth', function(Illuminate\Http\Request $request) {
            $result = Auth::attempt([
                'email' => $request->email,
                'password' => $request->password,
            ]);
        });
    }


    // Generate fake visits we can parse for metrics
    // (don't create them in database)
    protected function generateVisits($number, $timeInterval = '-1 week', $attributes = [])
    {
        $visits = [];
        for($x=0;$x<$number;$x++) {
            $visits[] = $this->makeVisit($attributes, $timeInterval);
        }   
        return new Collection($visits);    
    }

    // Generate fake visits we can parse for metrics
    // (don't create them in database)
    protected function generateVisitsByDates($number, Carbon $start, Carbon $end , $attributes = [])
    {
        $faker = $this->faker;
        $visits = [];
        for($x=0;$x<$number;$x++) {
            $attributes['date'] = $faker->dateTimeBetween($start,$end);
            $visits[] = $this->makeVisit($attributes);
        }   
        return new Collection($visits);    
    }

    // Generate fake visits and save them in database
    protected function createVisits($number, $timeInterval = '-1 week', $attributes = [])
    {
        $repo = $this->app->make(VisitRepository::class);
        $visits = $this->generateVisits($number, $timeInterval, $attributes);
        $visits->map(function($item) use ($repo) {
            $repo->store($item);
        });
    }

    protected function createVisitsByDate($number, $startDate, $endDate, $attributes = [])
    {
        $repo = $this->app->make(VisitRepository::class);
        $visits = $this->generateVisitsbyDates($number, $startDate, $endDate, $attributes);
        $visits->map(function($item) use ($repo) {
            $repo->store($item);
        });
    }

    protected function createVisitsInTimeInterval(TimeInterval $interval, $number, $attributes = [])
    {
        $this->createVisitsByDate($number, $interval->start(), $interval->end(), $attributes);
    }

    /**
     * Create Random visit in every subdivision (hour) of the given time interval
     * 
     * @param  TimeInterval $interval   
     * @param  integer      $number     number of visits per hour     
     * @param  array        $attributes 
     * @return void                   
     */
    protected function createVisitsInEveryTimeInterval(TimeInterval $interval, $number, $attributes = [])
    {
        foreach($interval->toHours() as $hour) {
            $this->createVisitsByDate($number, $hour->start(), $hour->end(), $attributes);
        }
    }

    /**
     * Generate some realistic sitemap from a website
     * 
     * @return array
     */
    protected function getUrlStack()
    {
        return [
            "/",
            "/artists",
            "/artists/john_zorn",
            "/artists/sphongle",
            "/artists/nirvana",
            "/artists/pj_harvey",
            '/artists/mano_solo',
            "/artists/metallica",
            "/artists/lou_reed",
            "/movies",
            '/movies/berlin_calling',
            '/movies/titanic',
            '/movies/mulholland_drive',
            '/movies/be_kind_rewind',
            '/movies/transpotting',
        ];
    }
 
    /**
     * Build a visit record. Attributes overrides generated data
     * 
     * @param  array  $attributes   manually set some attribute
     * @param  string $startDate    the time interval in the past to create the visits
     * @return Visit             
     */
    protected function makeVisit(array $attributes = [], $startDate = '-1 year')
    {
        $faker = $this->faker;
        $data = [
            'user_id' => null,
            'url' => $faker->randomElement($this->getUrlStack()),
            'user_agent' => $faker->userAgent,
            'ip' => $faker->randomElement([$faker->ipv4, $faker->ipv6]),
            'date' => $faker->dateTimeBetween($startDate),
            'cookie' => $faker->sha256,
            'referer' => '',
            'actions' => [],
            'custom' => [],
            'anonymous' => false,
            'session_id' => $faker->sha256,
            'status_code' => 200,
        ];
        foreach($attributes as $key => $value) {
            $data[$key] = $value;
        }

        return Visit::createFromArray($data);
    }

    /**
     * Create a User
     * 
     * @return App\User
     */
    protected function createTestUser()
    {
        $user = new App\User;
        $user->name = 'marty mc fly';
        $user->email = 'test@example.net';
        $user->password = bcrypt('test');
        $user->save();
        return $user;
    }

    /**
     * Assert all visit are unique to Cookie, useful to validate
     * some analyzers, consoliders
     * 
     * @return void
     */
    protected function assertVisitsAreUnique()
    {
        $cookies = VisitModel::pluck('cookie')->toArray();
        $this->assertEquals(count(array_unique($cookies)), count($cookies));
    }

    /**
     * Asserts that the response doesn't contain the given cookie.
     *
     * @param  string $cookieName
     * @return $this
     */
    protected function dontSeeCookie($cookieName)
    {
        $headers = $this->response->headers;
        $exist = false;
        foreach ($headers->getCookies() as $cookie) {
            if ($cookie->getName() === $cookieName) {
                $exist = true;
                break;
            }
        }
        $this->assertFalse($exist, "Cookie [{$cookieName}] was found.");
        return $this;
    }


    /**
     * Get last day as time interval
     * 
     * @return TimeInterval
     */
    protected function getLastDay()
    {
        $start = Carbon::now()->subDay(1)->startOfDay();
        $end = Carbon::now()->subDay(1)->endOfDay();
        
        return new TimeInterval($start, $end, Metric::DAILY);
    }

    /**
     * Get last month as time interval
     * 
     * @return TimeInterval
     */
    protected function getLastMonth()
    {
        $start = Carbon::now()->subMonth(1)->startOfMonth();
        $end = Carbon::now()->subMonth(1)->endOfMonth();
        
        return new TimeInterval($start, $end, Metric::MONTHLY);
    }

    /**
     * Get last year as time interval
     * 
     * @return TimeInterval
     */
    protected function getLastYear()
    {
        $start = Carbon::now()->subYear(1)->startOfYear();
        $end = Carbon::now()->subYear(1)->endOfYear();

        return new TimeInterval($start, $end, Metric::YEARLY);
    }

    /**
     * Create a collection of metric objects, by analyzing every hour
     * in the time interval
     * 
     * @param  AnalyzerInterface $analyzer   
     * @param  TimeInterval      $interval   
     * @param  integer           $visitCount 
     * @return Collection
     */
    protected function createMetrics(AnalyzerInterface $analyzer, TimeInterval $interval, $visitCount)
    {
        $metrics = new Collection;
        $this->createVisitsInTimeInterval($interval, $visitCount);
        $visits = $this->visits->getByTimeInterval($interval);
    
        $compiler = new Compiler([$analyzer]);

        foreach($interval->toHours() as $hour) {
            $hourVisits = $this->visits->getByTimeInterval($hour);
            $stats = $compiler->compile($hourVisits, $interval);
            $metrics->push(Metric::create($hour, $stats, count($hourVisits)));
        }
        return $metrics;
    }
}
