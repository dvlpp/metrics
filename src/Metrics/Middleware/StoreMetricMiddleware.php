<?php

namespace Dvlpp\Metrics\Middleware;

use Closure;
use Dvlpp\Metrics\Manager;
use Dvlpp\Metrics\Repositories\VisitRepository;

class StoreMetricMiddleware
{
    /**
     * @var VisitRepository
     */
    protected $visits;

    /**
     * @var Manager
     */
    protected $metricManager;

    public function __construct(VisitRepository $visits, Manager $metricManager)
    {
        $this->visits = $visits;
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
        return $next($request);
    }

    public function terminate($request, $response)
    {
        $visit = $this->metricManager->visit();

        if($visit && $this->metricManager->isRequestTracked()) {
            $this->visits->store($this->metricManager->visit());    
        }
        
    }
}
