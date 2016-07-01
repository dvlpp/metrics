<?php

use Dvlpp\Metrics\Action;
use Dvlpp\Metrics\Manager;

if (! function_exists('metrics')) {
    
    /**
     * Get the Metrics Manager instance
     *
     * @return Dvlpp\Metrics\Manager
     */
    function metrics()
    {
        return app(Manager::class);
    }
}

if (! function_exists('metrics_action')) {
    
    /**
     * Attach an action object to the current visit
     *
     * @return void
     */
    function metrics_action(Action $action)
    {
        return app(Manager::class)->action($action);
    }
}
