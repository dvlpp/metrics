<?php

namespace Dvlpp\Metrics\Analyzers;

use Illuminate\Support\Collection;
use Dvlpp\Metrics\Contracts\CompilerInterface;
use Dvlpp\Metrics\Contracts\ConsoliderInterface;

abstract class Analyzer implements CompilerInterface, ConsoliderInterface {

    /**
     * Indicate at which inteval this analyzer must be run. Possible values are 
     * 'hourly', 'daily', 'weekly', 'monthly', 'yearly'
     * 
     * @var string
     */
    protected $period = 'hourly';

    /**
     * Extract statistics from a range of visits
     * 
     * @param  Collection $visits [description]
     * @return array
     */
    public function compile(Collection $visits);


    /**
     * Consolidate several metrics objects
     * 
     * @param  Collection $metrics 
     * @return 
     */
    public function consolidate(Collection $metrics);

    /**
     * Get the time interval on which this can be calculated
     *
     * @return  string
     */
    public function getPeriod()
    {
        return $this->period;
    }
}
