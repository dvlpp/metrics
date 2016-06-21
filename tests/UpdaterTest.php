<?php

use Carbon\Carbon;
use Dvlpp\Metrics\Manager;
use Dvlpp\Metrics\TimeInterval;

class UpdaterTest extends MetricTestCase 
{
    protected $updater;

    public function setUp()
    {
        parent::setUp();
        $this->updater = $this->app->make(Manager::class)->getUpdater();
    }

    /** @test */
    public function we_can_get_all_due_intervals()
    {
        //$this->createVisits(1000, '-1 year');
        $period = new TimeInterval(Carbon::now()->subDay()->startOfDay(), Carbon::now());
        dd($this->updater->getDueIntervals($period));
    }

}
