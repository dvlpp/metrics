<?php

namespace Dvlpp\Metrics\Analyzers;

use Carbon\Carbon;
use Dvlpp\Metrics\Metric;
use Illuminate\Support\Collection;
use Dvlpp\Metrics\Repositories\VisitRepository;

class VisitorAnalyzer extends Analyzer
{
    /**
     * @var VisitRepositoty
     */
    protected $visits;

    /**
     * Session lifetime, in minutes
     * 
     * @var integer
     */
    protected $lifetime;

    public function __construct(VisitRepository $visits)
    {
        $this->visits = $visits;
        $this->lifetime = config('session.lifetime');

    }

    /**
     * Compile visits into array of statistics
     * 
     * @param  Collection $visits
     * @return array
     */
    public function compile(Collection $visits)
    {
        $count = 0;
        $sessionStack = [];

        foreach($visits as $visit) {

            $sessionId = $visit->getSessionId();
            
            if(! in_array($sessionId, $sessionStack)) {
                if(! $this->hasPreviousSession($sessionId, $visit->getDate())) {
                    $count++;
                }
                $sessionStack[] = $sessionId;
            }
        }
        
        return ['visitors' => $count];
    }

    /**
     * Consolidate statistics
     * 
     * @param  Collection $metrics 
     * @return array
     */
    public function consolidate(Collection $metrics)
    {
        $count = 0;

        foreach($metrics as $metric) {
            $statistic = $metric->getStatisticsByKey(get_class($this));
            if(array_key_exists('visitors', $statistic)) {
                $count+= $statistic['visitors'];
            }
        }
        
        return ['visitors' => $count];
    }

    /**
     * Chech if visitor has already a previous session
     * 
     * @param  string  $sessionId 
     * @param  Carbon  $visitDate 
     * @return boolean            
     */
    protected function hasPreviousSession($sessionId, Carbon $visitDate)
    {
        $from = $visitDate->subMinutes($this->lifetime);
        
        $visit = $this->visits->lastVisitBySession($sessionId, $from);

        return $visit ? false : true;
    }

}
