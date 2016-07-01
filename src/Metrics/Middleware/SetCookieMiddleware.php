<?php

namespace Dvlpp\Metrics\Middleware;

use Closure;
use Dvlpp\Metrics\Manager;

class SetCookieMiddleware
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
        $response = $next($request);

        $visit = $this->metricManager->visit();

        if($visit && $this->metricManager->isRequestTracked()) {
            $cookieName = config('metrics.cookie_name');
            $value = $visit->getCookie();
            $response->withCookie(cookie()->forever($cookieName, $value));  
        }

        return $response;
    }
}
