<?php

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

abstract class MetricTestCase extends Illuminate\Foundation\Testing\TestCase
{
    use InteractsWithDatabase;

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
