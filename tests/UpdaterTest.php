<?php

use Carbon\Carbon;
use Dvlpp\Metrics\Metric;
use Dvlpp\Metrics\Updater;
use Dvlpp\Metrics\Manager;
use Dvlpp\Metrics\TimeInterval;
use Dvlpp\Metrics\Repositories\Eloquent\MetricModel;

class UpdaterTest extends MetricTestCase 
{
    protected $updater;

    public function setUp()
    {
        parent::setUp();
        $this->updater = $this->app->make(Manager::class)->getUpdater();
    }

    /** @test */
    public function we_have_correct_start_and_end_periods()
    {
        $this->createVisits(1000, '-1 year');
        $expectedStart = Carbon::now()->subYear()->startOfYear();
        $result = $this->updater->getPeriodStart();
        $this->assertEquals($expectedStart,$result);

        $expectedEnd = Carbon::now()->subHour()->minute(59)->second(59);
        $result = $this->updater->getPeriodEnd();
        $this->assertEquals($expectedEnd,$result);

    } 

    /** @test */
    public function we_can_get_complete_periods()
    {
        $referenceStart = Carbon::create(2014,1,1,0,0,0);
        $referenceEnd = Carbon::create(2016,2,3,5,59,59);

        $periods = $this->updater->getCompletePeriods($referenceStart, $referenceEnd);

        // -> 2014,2015, Jan 2016, 1st February, 2nd February, 3rd Feb. 0h, 1h, 2h, 3h, 4h
        $this->assertCount(10, $periods);

        $referenceStart = Carbon::create(2014,1,1,0,0,0);
        $referenceEnd = Carbon::create(2014,2,3,5,59,59);

        $periods = $this->updater->getCompletePeriods($referenceStart, $referenceEnd);
        $this->assertCount(8, $periods);

    }

    /** @test */
    public function we_can_parse_for_missing_metrics()
    {
        $referenceStart = Carbon::create(2016,1,1,0,0,0);
        $referenceEnd = Carbon::create(2016,1,2,1,59,59);

        $periods = $this->updater->getCompletePeriods($referenceStart, $referenceEnd);
        $missing = $this->updater->parseForMissingMetrics($periods);

        // 1 day + 24 + 1 hours
        $this->assertCount(26, $missing);
    }

    /** @test */
    public function we_can_update_metrics_without_any_visit()
    {
        $this->updater->update();
    }

    /** @test */
    public function we_get_exact_visit_count()
    {
        $start = Carbon::create(2016,1,1,0,0,0);
        $end = Carbon::create(2016,1,1,23,59,59);
        $this->createVisitsByDate(50, $start, $end);
        $this->updater->update();
        $metrics = $this->app->make(Dvlpp\Metrics\Repositories\MetricRepository::class);
        $period = new TimeInterval($start, $end, Metric::DAILY);
        $metric = $metrics->find($period);
        $this->assertEquals(50, $metric->getCount());
    }

    /** @test */
    public function load_test()
    {
        $start = Carbon::create(2016,1,1,0,0,0);
        $end = Carbon::create(2016,1,1,23,59,59);
        $this->createVisitsByDate(100000, $start, $end);
        $this->updater->update();
    }
}
