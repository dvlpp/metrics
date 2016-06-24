<?php

namespace Dvlpp\Metrics;

use Illuminate\Support\Collection;

class Consolider
{
    /**
     * @var array
     */
    protected $consoliders;

    /**
     * @var string
     */
    protected $type;

    public function __construct(array $consoliders, $type)
    {
        $this->consoliders = $consoliders;
        $this->type = $type;
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
            $consoliderStatistics = [];

            foreach($metrics as $metric) {
                $consoliderStatistics[] = $metric->getStatisticsByKey(get_class($consolider));
            }

            $statistics[get_class($consolider)] = $consolider->consolidate($consoliderStatistics);
        }

        return $statistics;
    }

}