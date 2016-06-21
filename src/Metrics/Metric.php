<?php

namespace Dvlpp\Metrics;

use Carbon\Carbon;

class Metric
{
    /**
     * Period start
     * 
     * @var Carbon
     */
    protected $start;

    /**
     * Period End
     * 
     * @var Carbon
     */
    protected $end;

    /**
     * Embedded statistics
     * 
     * @var array
     */
    protected $statistics = [];

    /**
     * Visit count
     * 
     * @var integer
     */
    protected $count;

    public static function create(TimeInterval $interval, array $statistics, count)
    {
        $metric = new static;
        $metric->setStart($interval->start());
        $metric->setEnd($interval->end());
        $metric->setStatistics($statistics);
        $metric->setCount($count);
    }

    /**
     * Create a Metric object from an array
     * 
     * @param  array  $data [description]
     * @return Metric
     */
    public static function createFromArray(array $data)
    {
        $metric = new static;
        $metric->setStart($data['start']);
        $metric->setEnd($data['end']);
        $metric->setStatistics($data['statistics']);
        $metric->setCount($data['count']);
    }

    public function setStart(Carbon $start)
    {
        $this->start = $start;
    }

    public function setEnd(Carbon $end)
    {
        $this->end = $end;
    }

    public function setStatistics(array $statistics)
    {
        $this->statistics = $statistics;
    }

    public function setCount($count)
    {
        $this->count = $count;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function getStatistics()
    {
        return $this->statistics;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function toArray()
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
            'count' => $this->count,
            'statistics' => $this->statistics,
        ];
    }
}
