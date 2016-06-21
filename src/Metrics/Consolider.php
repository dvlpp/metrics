<?php

namespace Dvlpp\Metrics;

use Illuminate\Support\Collection;

class Consolider
{
    /**
     * @var array
     */
    protected $consoliders;

    public function __construct(array $consoliders)
    {
        $this->consoliders = $consoliders;
    }

    /**
     * Compile statistics from a collection of Metric objects
     * 
     * @param  Collection $metrics
     * @return array
     */
    public function consolidate(Collection $metrics)
    {
        $statistics = [];

        foreach($this->consoliders as $consolider) {
            $statistics[get_class($consolider)] = $consolider->consolidate($metrics);
        }

        return $statistics;
    }

}