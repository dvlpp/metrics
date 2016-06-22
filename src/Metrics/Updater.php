<?php

namespace Dvlpp\Metrics;

use Carbon\Carbon;
use Illuminate\Support\Collection;
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
        $start = $this->getPeriodStart();
        $end = $this->getPeriodEnd();

        // First, we'll get the complete period in the timeframe, with only the 
        // top level periods (meaning we'll only have the Year TimeInterval of a complete
        // year, not the month TimeInterval the years is composed)
        $completePeriods = $this->getCompletePeriods($start, $end);




        //$this->processAnalyzes();
        //$this->processConsolidations();

        //$metric = $this->compileTimeInterval($year, Metric::YEARLY);
    }

    public function parseForMissingMetrics($periods)
    {
        $missingMetrics = [];
        
        foreach($periods as $period) {
            if(! $this->metrics->find($period)) {
                
                $missingMetrics[] = $period;
                if($period->type() != Metric::HOURLY) {
                    $missingMetrics = array_merge($missingMetrics, $this->parseForMissingMetrics($period->divide()));
                }
            }
        }

        return $missingMetrics;
    }

    /**
     * Return the completed years, months, day, hours
     * 
     * @param  Carbon $start 
     * @param  Carbon $end  
     * @return  Collection
     */
    public function getCompletePeriods(Carbon $start, Carbon $end)
    {
        $periods = [];

        $periods = array_merge($periods, $this->getCompletePeriodsByType($start, $end, Metric::YEARLY));
        $start = $end->copy()->startOfYear();
        $periods = array_merge($periods, $this->getCompletePeriodsByType($start, $end, Metric::MONTHLY));
        $start = $end->copy()->startOfMonth();
        $periods = array_merge($periods, $this->getCompletePeriodsByType($start, $end, Metric::DAILY));
        $start = $end->copy()->startOfDay();
        $periods = array_merge($periods, $this->getCompletePeriodsByType($start, $end, Metric::HOURLY));
        
        return new Collection($periods);
    }

    /**
     * Get top-level complete periods for the given $start & $end
     * 
     * @param  Carbon $start
     * @param  Carbon $end  
     * @param  integer $type  
     * @return  array
     */
    protected function getCompletePeriodsByType(Carbon $start, Carbon $end, $type)
    {
        switch($type) {
            case Metric::YEARLY:
                $diff = $end->diffInYears($start);
                break;
            case Metric::MONTHLY:
                $diff = $end->diffInMonths($start);
                break;
            case Metric::DAILY:
                $diff = $end->diffInDays($start);
                break;
            case Metric::HOURLY:
                $diff = $end->diffInHours($start);
        }
       
        $intervals = [];

        for($x = 0; $x < $diff; $x++) {

            switch ($type) {
                case Metric::YEARLY:
                    $intervalStart = $start->copy()->addYears($x)->startOfYear();
                    $intervalEnd = $intervalStart->copy()->endOfYear();
                    break;
                case Metric::MONTHLY:
                    $intervalStart = $start->copy()->addMonths($x)->startOfMonth();
                    $intervalEnd = $intervalStart->copy()->endOfMonth();
                    break;
                case Metric::DAILY:
                    $intervalStart = $start->copy()->addDays($x)->startOfDay();
                    $intervalEnd = $intervalStart->copy()->endOfDay();
                    break;
                case Metric::HOURLY:
                    $intervalStart = $start->copy()->addHours($x)->minute(0)->second(0);
                    $intervalEnd = $intervalStart->copy()->minute(59)->second(59);
            }
            
            $intervals[] = new TimeInterval($intervalStart, $intervalEnd, $type);    
        }

        return $intervals;
    }

    /**
     * Get the start date for the Metric processing period
     * 
     * @return Carbon
     */
    public function getPeriodStart()
    {
        // First we'll check if metrics exists, and if so we'll make the first metric in time
        // the start of our reference period. 
        if($firstMetric = $this->metrics->first()) {
            $start = $firstMetric->getStart();
        }
        else {  
            $start = $this->visits->first()->getDate();
        }

        if($start) {
            return $start->startOfYear();
        }
        else {
            return null;
        }
    }

    /**
     * Get the end of the processing period, which will be always the end of the last
     * hour.
     * 
     * @return Carbon
     */
    public function getPeriodEnd()
    {
        return Carbon::now()->subHour()->minute(59)->second(59);
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
