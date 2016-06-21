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
        $this->metricManager->track(Visit::createFromRequest($request));
        
        return $next($request);
    }
}
