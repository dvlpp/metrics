<?php

use Carbon\Carbon;
use Dvlpp\Metrics\TimeInterval;

class IntervalGeneratorTest extends MetricTestCase
{
    /** @test */
    public function we_get_carbon_objects_from_time_intervel()
    {
        $start = Carbon::create(2016, 1, 1, 0, 0, 0);
        $end = Carbon::create(2016, 12, 31, 23, 59, 59);

        $timeInterval = new TimeInterval($start, $end);

        $this->assertInstanceOf(Carbon::class, $timeInterval->start());
        $this->assertInstanceOf(Carbon::class, $timeInterval->end());
    }

    /** @test */
    public function we_generate_only_full_years()
    {
        $start = Carbon::create(2016, 1, 1, 0, 0, 0);
        $end = Carbon::create(2016, 12, 31, 23, 59, 59);

        $generator = new TimeInterval($start, $end);

        $this->assertEquals([], $generator->years());
    }

    /** @test */
    public function we_can_generate_all_years()
    {
        $start = Carbon::create(2012, 1, 1);
        $end = Carbon::create(2016, 6, 21);
        $generator = new TimeInterval($start, $end);

        $this->assertCount(4, $generator->years());
    }

    /** @test */
    public function we_can_generate_months()
    {
        $start = Carbon::create(2016, 1, 20);
        $end = Carbon::create(2016, 12, 21);
        $generator = new TimeInterval($start, $end);

        $this->assertCount(11, $generator->months());
    }

     /** @test */
    public function we_generate_only_full_months()
    {
        $start = Carbon::create(2016, 6, 20);
        $end = Carbon::create(2016, 6, 21);
        $generator = new TimeInterval($start, $end);

        $this->assertEquals([], $generator->months());
    }

     /** @test */
    public function we_can_generate_days()
    {
        $start = Carbon::create(2016, 6, 20);
        $end = Carbon::create(2016, 6, 27);
        $generator = new TimeInterval($start, $end);

        $this->assertCount(7, $generator->days());
    }

     /** @test */
    public function we_generate_only_full_days()
    {
        $start = Carbon::create(2016, 6, 20, 10,0,0);
        $end = Carbon::create(2016, 6, 20,13,0,0);
        $generator = new TimeInterval($start, $end);

        $this->assertEquals([], $generator->days());
    }

     /** @test */
    public function we_can_generate_hours()
    {
        $start = Carbon::create(2016, 6, 21, 10,0,0);
        $end = Carbon::create(2016, 6, 21, 13,32,23);
        $generator = new TimeInterval($start, $end);

        $this->assertCount(3, $generator->hours());
    }

     /** @test */
    public function we_generate_only_full_hours()
    {
        $start = Carbon::create(2016, 6, 21, 10,0,0);
        $end = Carbon::create(2016, 6, 21, 10,32,23);
        $generator = new TimeInterval($start, $end);

        $this->assertEquals([], $generator->hours());
    }
}