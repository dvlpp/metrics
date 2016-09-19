<?php

namespace Dvlpp\Metrics\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Dvlpp\Metrics\Actions\UserLoginAction;
use Dvlpp\Metrics\Jobs\MarkPreviousUserVisits;

class LoginListener 
{
    use DispatchesJobs;

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
        $this->manager->action($action);

        // Then we tell the manager to go look back in time for untracked visits
        $job = new MarkPreviousUserVisits($event->user);
        $this->dispatch($job);
    }

}
