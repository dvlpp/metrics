<?php

namespace Dvlpp\Metrics\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Dvlpp\Metrics\Manager;

class RetroSessions implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Mark previous user visits
     * 
     * @return void
     */
    public function handle(Manager $manager)
    {
        $manager->updatePreviousSessions();
    }
}
