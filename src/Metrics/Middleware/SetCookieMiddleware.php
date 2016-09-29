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

            // If the tracking has been manually Set during
            // the request, we'll have to create the visit 
            // object there. 
            if(! $visit) {
                $visit = $this->visitCreator->createFromRequest($request);
                $this->metricManager->track($visit);
            }

            $value = $visit->getCookie();
            $cookieName = config('metrics.cookie_name');

            if($visit->isAnonymous() ) {
                $anonCookieName = config('metrics.anonymous_cookie_name');
                $response->withCookie(cookie()->forever($anonCookieName, $value));
                $response->withCookie(cookie()->forget($cookieName));
            }
            else {
                $response->withCookie(cookie()->forever($cookieName, $value)); 
            }   
        }

        return $response;
    }
}
