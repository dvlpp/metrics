<?php

use Carbon\Carbon;
use Dvlpp\Metrics\Metric;
use Dvlpp\Metrics\TimeInterval;

class TimeIntervalTest extends MetricTestCase
{
    /** @test */
    public function we_get_carbon_objects_from_time_interval()
    {
        $start = Carbon::create(2016, 1, 1, 0, 0, 0);
        $end = Carbon::create(2016, 12, 31, 23, 59, 59);

        $timeInterval = new TimeInterval($start, $end, Metric::YEARLY);

        $this->assertInstanceOf(Carbon::class, $timeInterval->start());
        $this->assertInstanceOf(Carbon::class, $timeInterval->end());
    }

    /** @test */
    public function a_year_divides_into_twelve_months()
    {
        $start = Carbon::create(2016, 1, 1, 0, 0, 0);
        $end = Carbon::create(2016, 12, 31, 23,59,59);

        $generator = new TimeInterval($start, $end, Metric::YEARLY);

        $this->assertCount(12, $generator->divide());
    }

     /** @test */
    public function a_month_divides_into_days()
    {
        $start = Carbon::create(2016, 6, 1);
        $end = Carbon::create(2016, 6, 30);
        $generator = new TimeInterval($start, $end, Metric::MONTHLY);

        $this->assertCount(30, $generator->divide());
    }

     /** @test */
    public function a_day_divides_into_hours()
    {
        $start = Carbon::create(2016, 6, 21, 0,0,0);
        $end = Carbon::create(2016, 6, 21, 23,59,59);
        $generator = new TimeInterval($start, $end, Metric::DAILY);

        $this->assertCount(24, $generator->divide());
    }

}