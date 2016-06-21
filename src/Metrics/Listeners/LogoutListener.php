<?php

namespace Dvlpp\Metrics\Listeners;

use Illuminate\Auth\Events\Logout;

class LogoutListener {

    protected $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Handle the event.
     *
     * @param Login $event
     * @return void
     */
    public function handle(Logout $event)
    {
        // We add a user login action
        $action = new UserLogoutAction($event->user->id);
        $this->manager->visit()->addAction($action);

        
    }

}
