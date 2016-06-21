<?php

use Faker\Factory;
use Dvlpp\Metrics\Visit;
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

        $this->migrateDatabase();
        $this->seedDatabase();
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

    protected function seedDatabase()
    {
        Model::unguard();
        
        //
        //
        Model::reguard();
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

    // Generate fake visits and save them in database
    protected function createVisits($number, $timeInterval = '-1 week', $attributes = [])
    {
        $repo = $this->app->make(VisitRepository::class);
        $visits = $this->generateVisits($number, $timeInterval, $attributes);
        $visits->map(function($item) use ($repo) {
            $repo->store($item);
        });
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
            'actions' => [],
            'custom' => [],
        ];
        foreach($attributes as $key => $value) {
            $data[$key] = $value;
        }

        return Visit::createFromArray($data);
    }
}

// Test debug helper
if(! function_exists('tdd'))
{
    $tdd_status = false;

    function tdd($expression)
    {
        global $tdd_status;

        if($tdd_status) dd($expression);
    }

    function setTddOn()
    {
        global $tdd_status;
        $tdd_status = true;
    }

    function setTddOff()
    {
        global $tdd_status;
        $tdd_status = false;
    }
}
