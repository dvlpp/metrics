<?php

namespace Dvlpp\Metrics\Middleware;

use Closure;
use Dvlpp\Metrics\Manager;
use Dvlpp\Metrics\VisitCreator;

class SetCookieMiddleware
{
    /**
     * @var Manager
     */
    protected $metricManager;

    /**
     * @var VisitCreator
     */
    protected $visitCreator;

    public function __construct(Manager $metricManager, VisitCreator $visitCreator)
    {
        $this->metricManager = $metricManager;
        $this->visitCreator = $visitCreator;
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
        
        if($this->metricManager->isRequestTracked())
        {
            $visit = $this->metricManager->visit();
            
            $value = $visit->getCookie();
            $cookieName = config('metrics.cookie_name');
            $anonCookieName = config('metrics.anonymous_cookie_name');
            if($visit->isAnonymous() ) {
                $response->headers->setCookie(cookie()->forever($anonCookieName, $value));
                $response->headers->setCookie(cookie()->forget($cookieName));
            }
            else {
                $response->headers->setCookie(cookie()->forever($cookieName, $value)); 
                $response->headers->setCookie(cookie()->forget($anonCookieName));
            }   
        }

        return $response;
    }
}
