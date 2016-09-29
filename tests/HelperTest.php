<?php

use Stubs\AcmeAction;

class HelperTest extends MetricTestCase {

    /** @test */
    public function we_can_call_metrics_function()
    {
        $metrics = metrics();
        $this->assertInstanceOf(\Dvlpp\Metrics\Manager::class, $metrics);
    }

    /** @test */
    public function we_can_call_action_function()
    {
        metrics_action(new AcmeAction('test'));
    }

    /** @test */
    public function we_can_call_anonymous_function()
    {
        $this->assertNull(metrics_is_anonymous());
    }    
}
