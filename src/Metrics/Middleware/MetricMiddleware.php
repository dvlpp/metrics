<?php

namespace Dvlpp\Metrics\Middleware;

use Closure;
use Dvlpp\Metrics\Visit;
use Dvlpp\Metrics\Manager;
use Dvlpp\Metrics\VisitCreator;

class MetricMiddleware
{
    /**
     * @var Manager
     */
    protected $metricManager;

    /**
     * @var VisitCreator
     */
    protected $visitCreator;

    public function __construct(Manager $metricManager, VisitCreator $creator)
    {
        $this->metricManager = $metricManager;
        $this->visitCreator = $creator;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Handle the 'Do Not Track' header
        if(! $request->server('HTTP_DNT') == 1 && $this->metricManager->isRequestTracked())
            /*config()->get('metrics.enable') && 
            config()->get('metrics.auto_track')) */
        {
            $this->metricManager->setTrackingOn();
            $visit = $this->visitCreator->createFromRequest($request);
            $this->metricManager->track($visit);
        }
        else {
            $this->metricManager->setTrackingOff();
        }
        
        return $next($request);
    }
}
