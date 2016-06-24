<?php

namespace Dvlpp\Metrics\Analyzers;

use Dvlpp\Metrics\Metric;
use Illuminate\Support\Collection;
use Dvlpp\Metrics\Contracts\AnalyzerInterface;
use Dvlpp\Metrics\Contracts\ConsoliderInterface;

abstract class Analyzer implements AnalyzerInterface, ConsoliderInterface {

    /**
     * Indicate at which inteval this analyzer must be run. 
     * 
     * @var integer
     */
    protected $period = Metric::HOURLY;

    /**
     * Extract statistics from a range of visits
     * 
     * @param  Collection $visits [description]
     * @return array
     */
    abstract public function compile(Collection $visits);


    /**
     * Consolidate several metrics objects
     * 
     * @param  array $statistics
     * @return 
     */
    abstract public function consolidate(array $statistics);

    /**
     * Get the time interval on which this can be calculated
     *
     * @return  string
     */
    public function getPeriod()
    {
        if(! in_array($this->period, [Metric::HOURLY, Metric::DAILY, Metric::MONTHLY, Metric::YEARLY])) {
            throw new MetricException("Analyzer period must be hour, day, month, or year");
        }
        return $this->period;
    }
}
