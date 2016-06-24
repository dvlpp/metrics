<?php

namespace Dvlpp\Metrics;

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
        return $this;
    }

    public function visit()
    {
        return $this->visit;
    }

    public function isRequestTracked()
    {
        return $this->trackRequest;
    }
        
    public function setTrackingOff()
    {
        $this->trackRequest = false;
    }

    /**
     * Look into past for unasigned visit entries with same User
     * 
     * @return void
     */
    /*public function lookIntoPast()
    {
        // TODO: find a 'ok' name for this method
    }*/

    /**
     * Add a custom data provider which will provide the Visit object
     * with a custom field at each request
     * 
     * @param Closure/Class $callback 
     * @return void
     */
    public function addDataProvider($callback)
    {   
        //
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
