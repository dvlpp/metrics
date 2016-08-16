<?php

namespace Dvlpp\Metrics;

use Closure;
use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Foundation\Application;
use Dvlpp\Metrics\Exceptions\TrackingException;
use Dvlpp\Metrics\Exceptions\MetricException;
use Dvlpp\Metrics\Repositories\MetricRepository;
use Dvlpp\Metrics\Repositories\VisitRepository;
use Dvlpp\Metrics\Contracts\AnalyzerInterface;
use Dvlpp\Metrics\Contracts\ConsoliderInterface;

class Manager
{   
    /**
     * Application instance
     * 
     * @var Application
     */
    protected $app;

    /**
     * @var Visit
     */
    protected $visit;

    /**
     * @var boolean
     */
    protected $trackRequest = true;

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $analyzers = [];

    /**
     * @var array
     */
    protected $consoliders = [];

    /**
     * @var array
     */
    protected $pendingActions = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Generate metrics for all due periods, if they don't exist yet
     * 
     *  @return void
     */
    public function updateMetrics()
    {
        return $this->getUpdater()->update();
    }

    /**
     * Instantiate & Return Updater
     * 
     * @return Update
     */
    public function getUpdater()
    {
        return new Updater(
            $this->app->make(MetricRepository::class), 
            $this->app->make(VisitRepository::class),
            $this->instantiateProcessors($this->getAnalyzersFromConfig()),
            $this->instantiateProcessors($this->getConsolidersFromConfig())
        );
    }

    /**
     * Set the tracking object 
     * 
     * @param  Visit  $visit [description]
     * @return Manager
     */
    public function track(Visit $visit)
    {
        if(! $this->isRequestTracked()) {
            return $this;
        }

        if($this->visit != null) {
            throw new TrackingException('Tracking object $visit cannot be set twice');
        }
        
        $this->visit = $visit;

        foreach($this->pendingActions as $action) {
            $this->visit->addAction($action);
        }

        return $this;
    }

    /**
     * Access the current visit
     * 
     * @return Visit
     */
    public function visit()
    {
        return $this->visit;
    }

    /**
     * Attach an action to the current visit
     * 
     * @param  Action $action
     * @return void
     */
    public function action(Action $action)
    {
        if($this->visit != null) {
            $this->visit->addAction($action);    
        }
        else {
            // In some cases (eg Middleware), the current Visit will
            // not be already initialized. So we'll stack it into
            // an array and add them when initialized.
            $this->pendingActions[] = $action;
        }
    }

    /**
     * Indicate if the tracking is enabled for the current request
     * 
     * @return boolean
     */
    public function isRequestTracked()
    {
        return $this->trackRequest;
    }
        
    /**
     * Disable tracking for current request
     *
     * @return void
     */
    public function setTrackingOff()
    {
        $this->trackRequest = false;
    }

    /**
     * Disable tracking for current request
     *
     * @return void
     */
    public function setTrackingOn()
    {
        $this->trackRequest = true;
    }

    /**
     * Look into past for the indicated user
     *
     * @param  string  $userId
     * @return boolean
     */
    public function markPreviousUserVisits($userId)
    {
        if($this->isRequestTracked() )
        {
            $timeMachine = $this->getTimeMachine();

            return $timeMachine->lookup($userId);
        }
        else {
            return false;
        }
    }

    /**
     * Get the time machine instance
     * 
     * @return TimeMachine
     */
    protected function getTimeMachine()
    {
        return new TimeMachine(
            $this->app->make(VisitRepository::class),
            $this->visit()
        );
    }

    /**
     * Add a custom data provider which will provide the Visit object
     * with a custom field at each request
     * 
     * @param Closure | class $callback 
     * @return void
     */
    public function addDataProvider($callback)
    {   
        $this->providers[] = $callback;
    }

    /**
     * Parse providers and execute them on visit instance
     * 
     * @return void
     */
    public function processDataProviders()
    {
        foreach($this->providers as $provider) {
            if($provider instanceof Closure) {
                $provider($this->visit);    
            }
            else {
                $provider = $this->app->make($provider);
                $provider->process($this->visit);
            }
            
        }
    }

    /**
     * Return the analyzers classes from config
     * 
     * @return array
     */
    protected function getAnalyzersFromConfig()
    {
        return $this->app['config']->get('metrics.analyzers');
    }

    /**
     * Return the consoliders classes from config
     * 
     * @return array
     */
    protected function getConsolidersFromConfig()
    {
        return $this->app['config']->get('metrics.consoliders');
    }

    /**
     * Convert processors class name into object instances
     * 
     * @param  array  $processorConfig 
     * @return  array
     */
    protected function instantiateProcessors(array $processorConfig)
    {
        $processors = [];

        foreach($processorConfig as $period => $classes) {
            $periodValue = $this->getPeriodConstantFromString($period);
            $processors[$periodValue] = [];
            foreach($classes as $class) {
                $processors[$periodValue][] =  $this->app->make($class);
            }
        }

        return $processors;
    }

    protected function getPeriodConstantFromString($period) 
    {
        switch($period) {
            case 'hourly':
                return Metric::HOURLY;
            case 'daily':
                return Metric::DAILY;
            case 'monthly':
                return Metric::MONTHLY;
            case 'yearly':
                return Metric::YEARLY;
        }
        throw new MetricException("Invalid period in config : $period");
    }

}
