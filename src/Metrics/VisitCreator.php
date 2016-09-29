<?php

namespace Dvlpp\Metrics;

use DateInterval;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Dvlpp\Metrics\Repositories\VisitRepository;

/**
 * Create a Visit object from a Request
 */
class VisitCreator
{
    /**
     * @var VisitRepository
     */
    protected $visits;

    /**
     * @var Manager
     */
    protected $manager;

    public function __construct(VisitRepository $visits, Manager $manager)
    {
        $this->visits = $visits;
        $this->manager = $manager;
    }

    /**
     * Create a Visit instance from a Request object
     * 
     * @param  Request $request
     * @return Visit
     */
    public function createFromRequest(Request $request)
    {
        $visit = new Visit;
        $visit->setDate(Carbon::now());
        $visit->setUrl($request->getUri());
        $visit->setReferer($request->server('HTTP_REFERER'));
        $visit->setIp($request->ip());

        $cookiePresent = $request->hasCookie(config('metrics.cookie_name'));
        $anonCookiePresent = $request->hasCookie(config('metrics.anonymous_cookie_name'));

        if($cookiePresent) {
            $visit->setAnonymous(false);
            $cookie = $request->cookies->get(config('metrics.cookie_name'));
        }

        if($anonCookiePresent) {
            $visit->setAnonymous(true);
            $cookie = $request->cookies->get(config('metrics.anonymous_cookie_name'));
        }

        if(($cookiePresent || $anonCookiePresent) && ! $this->hasCookieExpired($cookie)) {
            $visit->setCookie($cookie);
        }
        else {
            // If no cookie was found, we'll refer to the manager for which cookie to create
            $anonymousState = $this->manager->isAnonymous();
            $visit->setAnonymous($anonymousState);
            $visit->setCookie();
        }

        $visit->setUserAgent($request->server('HTTP_USER_AGENT') ? $request->server('HTTP_USER_AGENT') : 'undefined');
        return $visit;
    }

    /**
     * Check if the cookie has expired
     * 
     * @param  string  $cookie
     * @return boolean        
     */
    protected function hasCookieExpired($cookie)
    {
        $visit = $this->visits->oldestVisitForCookie($cookie);

        if($visit) {

            $lifetime = config('metrics.cookie_lifetime');
            $date = $visit->getDate();
            $maximumLifetimeDate = Carbon::now()->sub(DateInterval::createFromDateString($lifetime));
            if($date->lt($maximumLifetimeDate)) {
                return true;
            }
        }
        return false;
    }
}