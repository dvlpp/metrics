<?php

use Stubs\AcmeAction;
use Stubs\AcmeProvider;
use Dvlpp\Metrics\Manager;
use Dvlpp\Metrics\Visit;
use Dvlpp\Metrics\Repositories\Eloquent\VisitModel;
use Illuminate\Auth\Events\Login;
use Dvlpp\Metrics\TimeMachine;

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
    public function we_dont_log_a_visit_if_defaults_config_is_to_false()
    {
        $this->app['config']->set('metrics.auto_place_cookie', false);
        $this->visit("");
        $manager = $this->app->make(Manager::class);
        $visit = $manager->visit();
        $this->assertFalse($manager->isRequestTracked());
        $this->dontSeeCookie($this->app['config']->get('metrics.cookie_name'));
        $this->dontSeeInDatabase('metric_visits', [
            'cookie' => $visit->getCookie(),
        ]);
    }

    /** @test */
    public function we_log_a_visit_if_tracking_is_manually_during_request()
    {
        $this->app['config']->set('metrics.auto_place_cookie', false);
        $router = $this->app->make('router');
        $router->get('log', function(Manager $manager) {
            $manager->setTrackingOn();
        });
        $this->visit('/log');
        $manager = $this->app->make(Manager::class);
        $visit = $manager->visit();
        $this->assertNotNull($visit);
        $this->assertTrue($manager->isRequestTracked());
        $this->seeCookie($this->app['config']->get('metrics.cookie_name'));
        $this->seeInDatabase('metric_visits', [
            "cookie" => $visit->getCookie(),
        ]);
    }

    /** @test */
    public function we_log_a_visitor_who_already_have_a_metric_cookie()
    {
        $this->app['config']->set('metrics.auto_place_cookie', false);
        $cookieName = $this->app['config']->get('metrics.cookie_name');
        $cookies = [
            $cookieName => str_random(32),
        ];
        $result = $this->call('GET', '/', [], $cookies);
        $manager = $this->app->make(Manager::class);
        $visit = $manager->visit();
        $this->seeInDatabase('metric_visits', [
            "cookie" => $visit->getCookie(),
        ]);
        $this->seeCookie($this->app['config']->get('metrics.cookie_name'));
    }

    /** @test */
    public function we_log_a_visitor_who_already_have_an_anonymous_cookie()
    {
        $this->app['config']->set('metrics.auto_place_cookie', false);
        $cookieName = $this->app['config']->get('metrics.anonymous_cookie_name');
        $cookies = [
            $cookieName => str_random(32),
        ];
        $result = $this->call('GET', '/', [], $cookies);
        $manager = $this->app->make(Manager::class);
        $visit = $manager->visit();
        $this->seeInDatabase('metric_visits', [
            "cookie" => $visit->getCookie(),
        ]);
        $this->seeCookie($this->app['config']->get('metrics.anonymous_cookie_name'));
    }

    /** @test */
    public function we_log_a_visit_if_defaults_config_is_to_true()
    {
        $this->app['config']->set('metrics.auto_place_cookie', true);
        $this->visit("");
        $manager = $this->app->make(Manager::class);
        $this->assertInstanceOf(Visit::class, $manager->visit());
        $this->seeCookie($this->app['config']->get('metrics.cookie_name'));
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

    /** @test */
    public function login_the_user_triggers_the_time_machine()
    {   
        $user = $this->createTestUser();
        $timeMachine = Mockery::mock(TimeMachine::class);
        $timeMachine->shouldReceive('setCurrentVisit')->once();
        $timeMachine->shouldReceive('lookup')->once()->with($user->id);
        $this->app->bind(TimeMachine::class, function ($app) use ($timeMachine) {
            return $timeMachine;
        });

        $data = [
            'email' => 'test@example.net',
            'password' => 'test',
        ];
        //$this->expectsEvents(Login::class);
        $result = $this->post('auth', $data);
    }

    /** @test */
    public function we_dont_track_the_user_when_do_not_track_header_is_set()
    {
        $headers=['HTTP_DNT' => 1];
        $result = $this->get('/', $headers);
        $this->assertEquals(0, VisitModel::count());
    }

    /** @test */
    public function time_machine_wont_break_when_do_not_track_header_is_set()
    {
        $headers=['HTTP_DNT' => 1];
        $user = $this->createTestUser();
        $data = [
            'email' => 'test@example.net',
            'password' => 'test',
        ];
        $result = $this->post('auth', $data, $headers);
        $this->assertEquals(0, VisitModel::count());
    }


}
