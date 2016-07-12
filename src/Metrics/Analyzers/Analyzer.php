<?php

namespace Dvlpp\Metrics\Analyzers;

use Dvlpp\Metrics\Metric;
use Illuminate\Support\Collection;
use Dvlpp\Metrics\Contracts\AnalyzerInterface;
use Dvlpp\Metrics\Contracts\ConsoliderInterface;

abstract class Analyzer implements AnalyzerInterface, ConsoliderInterface {

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
     * @param  Collection $statistics
     * @return 
     */
    abstract public function consolidate(Collection $metrics);

}
