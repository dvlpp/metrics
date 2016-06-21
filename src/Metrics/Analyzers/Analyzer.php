<?php

namespace Dvlpp\Metrics\Analyzers;

use Illuminate\Support\Collection;
use Dvlpp\Metrics\Contracts\AnalyzerInterface;
use Dvlpp\Metrics\Contracts\ConsoliderInterface;

abstract class Analyzer implements AnalyzerInterface, ConsoliderInterface {

    /**
     * Indicate at which inteval this analyzer must be run. Possible values are 
     * 'hour', 'day', 'month', 'year'
     * 
     * @var string
     */
    protected $period = 'hour';

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
     * @param  Collection $metrics 
     * @return 
     */
    abstract public function consolidate(Collection $metrics);

    /**
     * Get the time interval on which this can be calculated
     *
     * @return  string
     */
    public function getPeriod()
    {
        if(! in_array($this->period, ['hour', 'day', 'month', 'year'])) {
            throw new MetricException("Analyzer period must be hour, day, month, or year");
        }
        return $this->period;
    }
}
