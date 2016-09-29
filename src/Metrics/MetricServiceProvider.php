<?php namespace Dvlpp\Metrics;

use Illuminate\Support\ServiceProvider;
use Dvlpp\Metrics\Middleware\MetricMiddleware;
use Dvlpp\Metrics\Repositories\VisitRepository;
use Dvlpp\Metrics\Repositories\MetricRepository;
use Dvlpp\Metrics\Middleware\SetCookieMiddleware;
use Dvlpp\Metrics\Middleware\NoTrackingMiddleware;
use Dvlpp\Metrics\Middleware\StoreMetricMiddleware;
use Dvlpp\Metrics\Repositories\Eloquent\VisitEloquentRepository;
use Dvlpp\Metrics\Repositories\Eloquent\MetricEloquentRepository;
use Dvlpp\Metrics\Listeners\LoginListener;
use Dvlpp\Metrics\Listeners\LogoutListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

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

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__.'/../config/config.php', $this->packageName);

        $this->app->bind(MetricRepository::class, MetricEloquentRepository::class);
        $this->app->bind(VisitRepository::class, VisitEloquentRepository::class);

        $this->app->singleton(Manager::class, function($app) {
            return new Manager($app);
        });

        if($this->app['config']->get('metrics.enable')) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependMiddleware(MetricMiddleware::class);
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->pushMiddleware(StoreMetricMiddleware::class);
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->pushMiddleware(SetCookieMiddleware::class);
        }

        $router = $this->app['router'];
        $router->middleware('no_tracking', NoTrackingMiddleware::class);

        $this->registerListeners();

        $this->commands([
            \Dvlpp\Metrics\Console\UpdateCommand::class,
            \Dvlpp\Metrics\Console\MigrateCommand::class,
        ]);
    }

    protected function registerListeners()
    {
        $events = $this->app['events'];
        
        if($this->app['config']->get('metrics.enable')) {
            $events->listen(Login::class, LoginListener::class);
            $events->listen(Logout::class, LogoutListener::class);
        }
    }

}
