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
    protected function createInterval($start, $end, $type = null)
    {
        return new static($start, $end, $type);
    }

    /**
     * Get the time interval for the current year
     * 
     * @return TImeInterval
     */
    public function currentYear()
    {
        return new static($this->end->copy()->startOfYear(), $this->end);
    }

    /**
     * Get the time interval for the current year
     * 
     * @return TImeInterval
     */
    public function currentMonth()
    {
         return new static($this->end->copy()->startOfMonth(), $this->end);
    }

    /**
     * Get the time interval for the current year
     * 
     * @return TImeInterval
     */
    public function currentDay()
    {
         return new static($this->end->copy()->startOfDay(), $this->end);
    }

    /**
     * Divide the time period
     * 
     * @return array
     */
    public function divide()
    {
        switch($this->type) {
            case Metric::YEARLY:
                return $this->months();
            case Metric::MONTHLY:
                return $this->days();
            case Metric::DAILY:
                return $this->hours();
            case Metric::HOURLY:
                return [];
        }

        $years = $this->years();
        if(count($years) > 0) {
            return $years;
        }
        $months = $this->months();
        if(count($months) > 0) {
            return $months;
        }
        $days = $this->days();
        if(count($days) > 0) {
            return $days;
        }
        $hours = $this->hours();
        if(count($hours) > 0) {
            return $hours;
        }
        return [];
    }

    /**
     * Get the remaining of a period after the largest divide() operation possibles
     * eg : if we have a period of a year and a half, it will return the later 
     * 
     * @return TimePeriod / null
     */
    public function trail()
    {
        // Full period, which have a type assigned won't have trails
        if($this->type > 0) {
            return null;
        }

        if(count($this->years()) > 0) {
            return $this->currentYear();
        }
        if(count($this->months()) > 0) {
            return $this->currentMonth();
        }
        if(count($this->days()) > 0) {
            return $this->currentDay();
        }
        return null;
    }

    /**
     * Return an array of TimeInterval objects corresponding to all full hours in the parent
     * period 
     * 
     * @return array
     */
    public function hours()
    {
        $hours = $this->end->copy()->addSecond()->diffInHours($this->start);

        $intervals = [];

        if($hours > 0) {
            for($x = 1; $x <= $hours; $x ++) {
                $date = $this->end->copy()->subHours($x);
                $start = $this->startOfHour($date->copy());
                $end = $this->endOfHour($date->copy());
                $intervals[] = $this->createInterval($start, $end, Metric::HOURLY);
            }
        }
        
        return $intervals;
    }

     /**
     * Return an array of TimeInterval objects corresponding to all full days in the parent
     * period 
     * 
     * @return array
     */
    public function days()
    {
        $days = $this->end->copy()->addSecond()->diffInDays($this->start);

        $intervals = [];

        if($days > 0) {
            for($x = 1; $x <= $days; $x ++) {
                $date = $this->end->copy()->subDays($x);
                $start = $date->copy()->startOfDay();
                $end = $date->copy()->endOfDay();
                $intervals[] = $this->createInterval($start, $end, Metric::DAILY);
            }
        }
        
        return $intervals;
    }

     /**
     * Return an array of TimeInterval objects corresponding to all full month in the parent
     * period 
     * 
     * @return array
     */
    public function months()
    {
        $months = $this->end->copy()->addSecond()->diffInMonths($this->start);

        $intervals = [];

        if($months > 0) {
            for($x = 1; $x <= $months; $x ++) {
                $date = $this->end->copy()->subMonths($x);
                $start = $date->copy()->startOfMonth();
                $end = $date->copy()->endOfMonth();
                $intervals[] = $this->createInterval($start, $end, Metric::MONTHLY);
            }
        }
        
        return $intervals;
    }

     /**
     * Return an array of TimeInterval objects corresponding to all full years in the parent
     * period 
     * 
     * @return array
     */
    public function years()
    {
        $years = $this->end->copy()->addSecond()->diffInYears($this->start);

        $intervals = [];

        if($years > 0) {
            for($x = 1; $x <= $years; $x ++) {
                $date = $this->end->copy()->subYears($x);
                $start = $date->copy()->startOfYear();
                $end = $date->copy()->endOfYear();
                $intervals[] = $this->createInterval($start, $end, Metric::YEARLY);
            }
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
