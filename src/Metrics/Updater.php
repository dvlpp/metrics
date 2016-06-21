<?php

namespace Dvlpp\Metrics;

use Carbon\Carbon;
use Dvlpp\Metrics\Repositories\MetricRepository;
use Dvlpp\Metrics\Repositories\VisitRepository;

class Updater 
{
    /**
     * @var MetricRepository
     */
    protected $metrics;

    /**
     * @var VisitRepository
     */
    protected $visits;

    /**
     * @var array
     */
    protected $analyzers;

    /**
     * @var array
     */
    protected $consoliders;


    public function __construct(MetricRepository $metrics, VisitRepository $visits, array $analyzers, array $consoliders)
    {
        $this->metrics = $metrics;
        $this->visits = $visits;
    }

    public function update()
    {
        $dueIntervals = $this->getDueIntervals();

        $firstVisit = $this->visits->first();

        $baseInterval = new TimeInterval($firstVisit->getDate(), Carbon::now());


        //$this->processAnalyzes();
        //$this->processConsolidations();

        //$metric = $this->compileTimeInterval($year, Metric::YEARLY);
    }

   
    public function getDueIntervals(TimeInterval $baseInterval, array $stack = [])
    {
        if ($baseInterval->type() !== null) {
        
            if ($this->metrics->hasTimeInterval($baseInterval)) {
                return $stack;
            }
            $stack[] = $baseInterval; 
        }

        $subIntervals = $baseInterval->divide();
        
        foreach($subIntervals as $subInterval) {
            $stack = $this->getDueIntervals($subInterval, $stack);
        }

        $trail = $baseInterval->trail();

        if($trail) {
            $stack = $this->getDueIntervals($trail, $stack);
        }

        return $stack;
    }

    /**
     * Compile a metric object for the given time interval
     * 
     * @param  TimeInterval $interval
     * @param  string $type
     * @return Metric
     */
    protected function compileTimeInterval(TimeInterval $interval, $type)
    {
        $compiler = new Compiler($this->analyzers, $type);
        $consolider = new Consolider($this->consoliders, $type);

        $visits = $this->visits->getTimeInterval($interval);
        $metrics = $this->metrics->getTimeInterval($interval);

        $statistics = array_merge($compiler->compile($visits), $consolider->consolidate($metrics));

        return Metric::create($type, $interval, $statistics, count($visits));
    } 

    /**
     * Run all consoliders on given time interval
     * 
     * @param  TimeInterval $interval
     * @return  Metric
     */
    protected function consolidateTimeInterval(TimeInterval $interval, $type)
    {
        $repository = $this->app->make(MetricRepository::class);
        

        $consolider = new Consolider($this->consoliders);
        $statistics = $consolider->consolidate($metrics, $type);

        $count = $metrics->reduce(function($previous, $item) {
            return $previous + $item->getCount();
        });

        return Metric::create($interval, $statistics, $count);
    }
}
