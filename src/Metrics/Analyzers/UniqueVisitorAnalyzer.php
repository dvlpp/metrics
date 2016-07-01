<?php

namespace Dvlpp\Metrics\Analyzers;

use Dvlpp\Metrics\Metric;
use Illuminate\Support\Collection;

class UniqueVisitorAnalyzer extends Analyzer
{

    public function compile(Collection $visits)
    {
        $data = [];

        $cookieStack = [];
        
        foreach($visits as $visit) {
            if(! in_array($visit->getCookie(), $cookieStack)) {
                $cookieStack[] = $visit->getCookie();
            }
        }

        return ['unique-visitors' => count($cookieStack)];

    }

    // This operation will add two array returned by the compile() method
    // then return a consolidated array. 
    public function consolidate(array $statistics)
    {
        $data = [];
        return $data;
    }


}