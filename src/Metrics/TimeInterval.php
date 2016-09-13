<?php

namespace Dvlpp\Metrics;

use Carbon\Carbon;

// This is a simple value object to simplify time interval handling, and 
// preserve its integrity by only delivering copy of Carbon objects.
class TimeInterval {

    protected $start;

    protected $end;

    protected $type;

    public function __construct(Carbon $start, Carbon $end, $type = null)
    {
        // We'll use copy() to make sure no other portion of the code
        // will modify the Carbon objects
        $this->start = $start->copy();
        $this->end = $end->copy();
        $this->type = $type;
    }

    public function type()
    {
        return $this->type;
    }

    /**
     * Return the start of the time interval
     * 
     * @return Carbon
     */
    public function start()
    {
        return $this->start->copy();
    }

    /**
     * Return the end of the time interval
     * 
     * @return Carbon
     */
    public function end()
    {
        return $this->end->copy();
    }

    /**
     * Create a new interval object for the given dates
     * 
     * @param  Carbon $start
     * @param  Carbon $end 
     * @return  TimeInterval
     */
    protected function createInterval($start, $end, $type)
    {
        return new static($start, $end, $type);
    }

    /**
     * Divide the time period
     * 
     * @return array
     */
    public function divide()
    {
        if($this->type == Metric::HOURLY) {
            return [];
        }
        else {
            return $this->divideByType($this->type);
        }
    }

    public function divideByType($type)
    {
        $start = $this->start->copy();

        switch($type) {
            case Metric::YEARLY:
                $basePeriod = 'year';
                $dividePeriod = 'month';
                break;
            case Metric::MONTHLY:
                $basePeriod = 'month';
                $dividePeriod = 'day';
                break;
            case Metric::DAILY:
                $basePeriod = 'day';
                $dividePeriod = 'hour';
        }

        $endMethod = 'endOf'.ucfirst($dividePeriod);
        $addMethod = 'add'.ucfirst($dividePeriod);

        $base = $start->$basePeriod;

        $intervals = [];

         // We'll just dumbly add time units and create interval until the base period
         // changed, so we'll let Carbon handle different month lenghts, and timezone, 
         // DST behaviour for us.
        while($start->$basePeriod == $base) {
            
            // Compensating for the missing Carbon endOfHour() method...
            if($type == Metric::DAILY) {
                $end = $this->endOfHour($start->copy());
            }
            else {
                $end = $start->copy()->$endMethod();
            }
            $intervals[] = new TimeInterval($start, $end, $type - 1);
            $start->$addMethod();
        }

        return $intervals;
    }

    protected function startOfHour(Carbon $date)
    {
        return $date->minute(0)->second(0);
    }

    protected function endOfHour(Carbon $date)
    {
        return $date->minute(59)->second(59);
    }
}
