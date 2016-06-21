<?php

namespace Dvlpp\Metrics;

use Carbon\Carbon;

// This is a simple value object to simplify time interval handling, and 
// preserve its integrity by only delivering copy of Carbon objects.
class TimeInterval {

    protected $start;

    protected $end;

    public function __construct(Carbon $start, Carbon $end)
    {
        // We'll use copy() to make sure no other portion of the code
        // will modify the Carbon objects
        $this->start = $start->copy();
        $this->end = $end->copy();
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

    protected function createInterval($start, $end)
    {
        return new static($start, $end);
    }

    public function hours()
    {
        $hours = $this->end->diffInHours($this->start);

        $intervals = [];

        if($hours > 0) {
            for($x = 1; $x <= $hours; $x ++) {
                $date = $this->end->copy()->subHours($x);
                $start = $this->startOfHour($date->copy());
                $end = $this->endOfHour($date->copy());
                $intervals[] = $this->createInterval($start, $end);
            }
        }
        
        return $intervals;
    }

    public function days()
    {
        $days = $this->end->diffInDays($this->start);

        $intervals = [];

        if($days > 0) {
            for($x = 1; $x <= $days; $x ++) {
                $date = $this->end->copy()->subDays($x);
                $start = $date->copy()->startOfDay();
                $end = $date->copy()->endOfDay();
                $intervals[] = $this->createInterval($start, $end);
            }
        }
        
        return $intervals;
    }

    public function months()
    {
        $months = $this->end->diffInMonths($this->start);

        $intervals = [];

        if($months > 0) {
            for($x = 1; $x <= $months; $x ++) {
                $date = $this->end->copy()->subMonths($x);
                $start = $date->copy()->startOfMonth();
                $end = $date->copy()->endOfMonth();
                $intervals[] = $this->createInterval($start, $end);
            }
        }
        
        return $intervals;
    }

    public function years()
    {
        $years = $this->end->diffInYears($this->start);

        $intervals = [];

        if($years > 0) {
            for($x = 1; $x <= $years; $x ++) {
                $date = $this->end->copy()->subYears($x);
                $start = $date->copy()->startOfYear();
                $end = $date->copy()->endOfYear();
                $intervals[] = $this->createInterval($start, $end);
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
