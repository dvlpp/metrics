<?php

namespace Dvlpp\Metrics\Listeners;

use Log;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Dvlpp\Metrics\Actions\UserLoginAction;
use Dvlpp\Metrics\Jobs\MarkPreviousUserVisits;
use Dvlpp\Metrics\Jobs\RetroSessions;
use Dvlpp\Metrics\Manager;

class LoginListener 
{
    use DispatchesJobs;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Request
     */
    protected $request;

    public function __construct(Manager $manager, Request $request)
    {
        $this->manager = $manager;
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param Login $event
     * @return void
     */
    public function handle(Login $event)
    {
        if($this->manager->isRequestTracked() && ! $this->manager->visit()->isAnonymous())
        {
            if(config('metrics.logging')) {
                $url = $this->request->url();
                Log::info('metrics : update user visits from '.$url);
            }
            // We add a user login action
            $action = new UserLoginAction($event->user->id);
            $this->manager->action($action);

            // Then we tell the manager to go look back in time for untracked visits
            $job = new MarkPreviousUserVisits($event->user);
            
            $this->dispatchNow($job);

            $retro = new RetroSessions();
            $this->dispatchNow($retro);
        }
    }

}
