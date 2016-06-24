<?php

namespace Dvlpp\Metrics\Consoliders;

use Dvlpp\Metrics\Metric;
use Illuminate\Support\Collection;
use Dvlpp\Metrics\Contracts\ConsoliderInterface;

abstract class Consolider implements ConsoliderInterface {

    /**
     * Indicate at which inteval this analyzer must be run. 
     * 
     * @var integer
     */
    protected $period = Metric::DAILY;

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
        if(! in_array($this->period, [Metric::DAILY, Metric::MONTHLY, Metric::YEARLY])) {
            throw new MetricException("Consolider period must day, month, or year");
        }
        return $this->period;
    }
}
