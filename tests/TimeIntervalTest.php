<?php

use Carbon\Carbon;
use Dvlpp\Metrics\Metric;
use Dvlpp\Metrics\TimeInterval;

class TimeIntervalTest extends MetricTestCase
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
        $end = Carbon::create(2016, 12, 31);

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
    public function a_full_day_has_24_hours()
    {
        $start = Carbon::create(2016, 6, 21, 0,0,0);
        $end = Carbon::create(2016, 6, 22, 0,0,0);
        $generator = new TimeInterval($start, $end);

        $this->assertCount(24, $generator->hours());
    }

     /** @test */
    public function we_generate_only_full_hours()
    {
        $start = Carbon::create(2016, 6, 21, 10,0,0);
        $end = Carbon::create(2016, 6, 21, 10,32,23);
        $generator = new TimeInterval($start, $end);

        $this->assertEquals([], $generator->hours());
    }

    /** @test */
    public function we_can_divide_to_the_largest_unit()
    {
        $start = Carbon::create(2015, 1, 1, 0,0,0);
        $end = Carbon::create(2016, 6, 21, 10,32,23);
        $interval = new TimeInterval($start, $end);

        $result = $interval->divide();
        $this->assertCount(1, $result);
        $this->assertEquals(Metric::YEARLY, $result[0]->type());

        setTddOn();

        $start = Carbon::create(2016, 5, 1, 0,0,0);
        $end = Carbon::create(2016, 6, 21, 10,32,23);
        $interval = new TimeInterval($start, $end);

        $result = $interval->divide();
        $this->assertCount(1, $result);
        $this->assertEquals(Metric::MONTHLY, $result[0]->type());

        $start = Carbon::create(2016, 6, 20, 0,0,0);
        $end = Carbon::create(2016, 6, 21, 10,32,23);
        $interval = new TimeInterval($start, $end);

        $result = $interval->divide();
        $this->assertCount(1, $result);
        $this->assertEquals(Metric::DAILY, $result[0]->type());

        $start = Carbon::create(2016, 6, 21, 0,0,0);
        $end = Carbon::create(2016, 6, 21, 10,32,23);
        $interval = new TimeInterval($start, $end);

        $result = $interval->divide();
        $this->assertCount(10, $result);
        $this->assertEquals(Metric::HOURLY, $result[0]->type());
    }

    /** @test */
    public function we_can_get_the_trail_after_dividing()
    {
        $start = Carbon::create(2015, 1, 1, 0,0,0);
        $end = Carbon::create(2016, 6, 21, 10,32,23);
        $interval = new TimeInterval($start, $end);

        $trail = $interval->trail();
        $this->assertEquals(Carbon::create(2016,1,1,0,0,0), $trail->start());
        $this->assertEquals(Carbon::create(2016, 6, 21, 10,32,23), $trail->end());

        $start = Carbon::create(2016, 1, 1, 0,0,0);
        $end = Carbon::create(2016, 6, 21, 10,32,23);
        $interval = new TimeInterval($start, $end);

        $trail = $interval->trail();
        $this->assertEquals(Carbon::create(2016,6,1,0,0,0), $trail->start());
        $this->assertEquals(Carbon::create(2016, 6, 21, 10,32,23), $trail->end());

        $start = Carbon::create(2016, 6, 1, 0,0,0);
        $end = Carbon::create(2016, 6, 21, 10,32,23);
        $interval = new TimeInterval($start, $end);

        $trail = $interval->trail();
        $this->assertEquals(Carbon::create(2016, 6, 21,0,0,0), $trail->start());
        $this->assertEquals(Carbon::create(2016, 6, 21, 10,32,23), $trail->end());


    }
}