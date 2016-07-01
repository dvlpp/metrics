<?php

namespace Dvlpp\Metrics;

use Closure;
use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Foundation\Application;
use Dvlpp\Metrics\Exceptions\TrackingException;
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
            $this->instantiateAnalyzers(),
            $this->instantiateConsoliders()
        );
    }

    /**
     * Add an analyzer class to the stack
     * 
     * @param string $analyzer
     * @return void
     */
    public function registerAnalyzer($analyzer)
    {
        $this->analyzers[] = $analyzer;
    }
    
    /**
     * Add a consolider class
     * 
     * @param string $consolider 
     * @return void
     */
    public function registerConsolider($consolider)
    {
        $this->consoliders[] = $consolider;
    }

    /**
     * [track description]
     * @param  Visit  $visit [description]
     * @return [type]        [description]
     */
    public function track(Visit $visit)
    {
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
     * Look into past for the indicated user
     *
     * @param  string  $userId
     * @return boolean
     */
    public function markPreviousUserVisits($userId)
    {
        $timeMachine = $this->getTimeMachine();

        return $timeMachine->lookup($userId);
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
     * Instantiate all registered analyzers & consoliders
     * 
     * @return array
     */
    protected function instantiateConsoliders()
    {

        $allConsoliders = array_merge($this->analyzers, $this->consoliders);

        $consoliders = [];

        foreach($allConsoliders as $consolider) {
            $consoliderObject = $this->app->make($consolider);

            if(! $consoliderObject instanceof ConsoliderInterface) {
                throw new InvalidArgumentException("Invalid Consolider Object");
            }

            $consoliders[$consolider] = $consoliderObject;
        }
        
        return $consoliders;
    }

    /**
     * Instantiate all analyzers and return them as an array
     * 
     * @return array
     */
    protected function instantiateAnalyzers()
    {
        $analyzers = [];

        foreach($this->analyzers as $analyzer) {
            $analyzerObject = $this->app->make($analyzer);

            if(! $analyzerObject instanceof AnalyzerInterface) {
                throw new InvalidArgumentException("Invalid Analyzer Object");
            }

            $analyzers[$analyzer] = $analyzerObject;
        }

        return $analyzers;
    }

}
