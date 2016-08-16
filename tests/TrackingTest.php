<?php

use Stubs\AcmeAction;
use Stubs\AcmeProvider;
use Dvlpp\Metrics\Manager;
use Dvlpp\Metrics\Visit;

class TrackingTest extends MetricTestCase
{
    protected $baseUrl = '/';

    public function setUp() 
    {
        parent::setUp();
        $this->app['config']->set('app.cipher', 'AES-256-CBC');
        $this->app['config']->set('app.key', str_random(32));
    }

    /** @test */
    public function we_log_a_visit_by_default()
    {
        $this->visit("");
        $manager = $this->app->make(Manager::class);
        $this->assertInstanceOf(Visit::class, $manager->visit());
    }

    /** @test */
    public function we_can_attach_an_action()
    {
        // Just to make sure Visit object is instantiated
        $this->visit("");
        $manager = $this->app->make(Manager::class);
        $manager->action(new AcmeAction('test'));
        $this->assertCount(1, $manager->visit()->actions());
    }

    /** @test */
    public function we_can_add_custom_data_providers_as_closure()
    {
        $this->visit("");
        $manager = $this->app->make(Manager::class);
        $manager->addDataProvider(function($visit) {
            $visit->setCustomValue('test', 'test');
        });
        $manager->processDataProviders();
        $this->assertTrue($manager->visit()->hasCustomValue('test'));
    }

    /** @test */
    public function we_can_add_custom_data_providers_as_class()
    {
        $this->visit("");
        $manager = $this->app->make(Manager::class);
        $manager->addDataProvider(AcmeProvider::class);
        $manager->processDataProviders();
        $this->assertTrue($manager->visit()->hasCustomValue('test'));
    }

    /** @test */
    public function calling_tracking_methods_wont_fail_if_tracking_is_off()
    {
        $manager = $this->app->make(Manager::class);
        $manager->setTrackingOff();
        $manager->action(new AcmeAction('test'));
        $manager->markPreviousUserVisits(1);
    }
}
