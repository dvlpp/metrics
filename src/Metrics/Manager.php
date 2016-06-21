<?php

namespace Dvlpp\Metrics;

use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Foundation\Application;
use Dvlpp\Metrics\Exceptions\TrackingException;
use Dvlpp\Metrics\Repositories\MetricRepository;
use Dvlpp\Metrics\Repositories\VisitRepository;
use Dvlpp\Metrics\Contracts\ConsoliderInterface;

class Manager
{
    /*public static const Yearly = 0;
    public static const Monthly = 1;
    public static const Weekly = 2;
    public static const Daily = 3;
    public static const Hourly = 4;*/
    
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
        $visitRepository = $this->app->make(VisitRepository::class);
        
        $firstVisit = $visitRepository->first();

        $interval = new TimeInterval($firstVisit->getDate(), Carbon::now());

        $metricRepository = $this->app->make(MetricRepository::class);
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
    
    public function registerConsolider($consolider)
    {
        $this->consoliders[] = $consolider;
    }

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
     * Compile a metric object for the given time interval
     * 
     * @param  TimeInterval $interval
     * @return Metric
     */
    protected function compileTimeInteval(TimeInterval $interval)
    {
        $repository = $this->app->make(VisitRepository::class);
        $visits = $repository->getTimeInterval($interval);

        $compiler = new Compiler($this->instantiateAnalyzers());
        $statistics = $compiler->compile($visits);

        return Metric::create($interval, $statistics, count($visits));
    } 

    /**
     * Run all consoliders on given time interval
     * 
     * @param  TimeInterval $interval
     * @return  Metric
     */
    protected function consolidateTimeInterval(TimeInterval $interval)
    {
        $repository = $this->app->make(MetricRepository::class);
        $metrics = $repository->getTimeInterval($interval);

        $consolider = new Consolider($this->instantiateConsoliders());
        $statistics = $consolider->consolidate($metrics);

        $count = $metrics->reduce(function($previous, $item) {
            return $previous + $item->getCount();
        });

        return Metric::create($interval, $statistics, $count);
    }

    /**
     * Instantiate all registered analyzers & consoliders
     * 
     * @return array
     */
    protected function instantiateConsoliders()
    {
        $allConsoliders = array_merge($this->analyzer, $this->consoliders);

        $consoliders = [];

        foreach($allConsoliders as $consolider) {
            $consoliderObject = $this->app->make($consolider);

            if(! $consoliderObject instanceof ConsoliderInterface) {
                throw new InvalidArgumentException("Invalid Consolider Object");
            }

            $consoliders[$consolider] = $consoliderObject;
        }
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

            if(! $analyzerObject instanceof Analyzer) {
                throw new InvalidArgumentException("Invalid Analyzer Object");
            }

            $analyzers[$analyzer] = $analyzerObject;
        }

        return $analyzers;
    }

}
