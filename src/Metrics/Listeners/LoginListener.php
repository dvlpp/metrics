<?php

namespace Dvlpp\Metrics\Listeners;

use Illuminate\Auth\Events\Login;
use Dvlpp\Metrics\Manager;
use Dvlpp\Metrics\Actions\UserLoginAction;

class LoginListener {

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
    public function handle(Login $event)
    {
        // We add a user login action
        $action = new UserLoginAction($event->user->id);
        $this->manager->visit()->addAction($action);

        // Then we tell the manager to go look back in time for untracked visits
        $this->manager->markPreviousUserVisits($event->user->id);
    }

}
