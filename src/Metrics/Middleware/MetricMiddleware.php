<?php

namespace Dvlpp\Metrics\Middleware;

use Closure;
use Dvlpp\Metrics\Visit;
use Dvlpp\Metrics\Manager;

class MetricMiddleware
{
    /**
     * @var Manager
     */
    protected $metricManager;

    public function __construct(Manager $metricManager)
    {
        $this->metricManager = $metricManager;
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
        if(! $request->server('HTTP_DNT') == 1) {
            $this->metricManager->track(Visit::createFromRequest($request));
        }
        else {
            $this->metricManager->setTrackingOff();
        }
        
        return $next($request);
    }
}
