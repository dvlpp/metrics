<?php

namespace Dvlpp\Metrics;

use Dvlpp\Metrics\Repositories\VisitRepository;

class TimeMachine
{
    /**
     * @var VisitRepository
     */
    protected $visits;

    /**
     * @var Visit
     */
    protected $currentVisit;

    public function __construct(VisitRepository $visits)
    {
        $this->visits = $visits;
    }

    /**
     * Set Current Visit
     * 
     * @param Visit $currentVisit 
     */
    public function setCurrentVisit(Visit $currentVisit)
    {
        $this->currentVisit = $currentVisit;
    }

    /**
     * Parse previous visits and update them with UserId
     * 
     * @param  string $userId
     * @return boolean
     */
    public function lookup($userId)
    {
        $currentCookie = $this->currentVisit->getCookie();

        // First, we'll make a query to retrieve what cookie was used
        // by the same user in its last visit, if any. We'll compare it
        // to the cookie used in the current visit.
        $lastVisit = $this->visits->lastVisitFromUser($userId);

        if($lastVisit === null) {
            return false;
        }

        if($currentCookie != $lastVisit->getCookie())
        {
            // If the value is different, we'll set the current visit's cookie
            // to the one used in last visit, and update any previous visit instance
            // to the same value.
            $this->currentVisit->setCookie($lastVisit->getCookie());

            $this->visits->translateCookie($currentCookie, $lastVisit->getCookie());

            $currentCookie = $lastVisit->getCookie();
        }

        // Then, we'll update previous visits corresponding to the user's cookie
        // with the user_id, so we make it simple to reconstitute a user's visit sequence.
        $this->visits->setUserForCookie($currentCookie, $userId);
        
        return true;
    }

}
