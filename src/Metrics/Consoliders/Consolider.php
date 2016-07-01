<?php

namespace Dvlpp\Metrics\Consoliders;

use Dvlpp\Metrics\Metric;
use Illuminate\Support\Collection;
use Dvlpp\Metrics\Contracts\ConsoliderInterface;

abstract class Consolider implements ConsoliderInterface {

    /**
     * Consolidate several metrics objects
     * 
     * @param  Collection $metrics 
     * @return 
     */
    abstract public function consolidate(Collection $metrics);

}
