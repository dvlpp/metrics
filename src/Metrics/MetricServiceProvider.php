<?php namespace Dvlpp\Metrics;

use Illuminate\Support\ServiceProvider;
use Dvlpp\Metrics\Middleware\MetricMiddleware;

/**
 * A Laravel 5's package template.
 *
 * @author: Rémi Collin 
 */
class MetricServiceProvider extends ServiceProvider {

    /**
     * This will be used to register config & view in 
     * your package namespace.
     *
     * --> Replace with your package name <--
     */
    protected $packageName = 'metrics';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        // Register your migration's publisher
        $this->publishes([
            __DIR__.'/../database/migrations/' => base_path('/database/migrations')
        ], 'migrations');
        
        // Publish your config
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path($this->packageName.'.php'),
        ], 'config');

        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__.'/../config/config.php', $this->packageName);

        $this->app[\Illuminate\Contracts\Http\Kernel::class]->pushMiddleware(MetricMiddleware::class);
    }

}
