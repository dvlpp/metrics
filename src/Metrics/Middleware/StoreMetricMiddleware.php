<?php

namespace Dvlpp\Metrics\Middleware;

use Closure;
use Dvlpp\Metrics\Manager;
use Dvlpp\Metrics\Repositories\VisitRepository;
use Illuminate\Contracts\Auth\Guard;

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

    /**
     * @var Guard
     */
    protected $guard;

    public function __construct(VisitRepository $visits, Manager $metricManager, Guard $guard)
    {
        $this->visits = $visits;
        $this->metricManager = $metricManager;
        $this->guard = $guard;
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

            // As some authentication method will take place
            // after the middleware are executed, we'll wait
            // for this last moment to set the user id, if 
            // present.
            if($this->guard->user()) {
                $visit->setUserId($this->guard->user()->id);
            }

            $this->metricManager->processDataProviders();

            $this->visits->store($this->metricManager->visit());    
        }
        
    }
}
