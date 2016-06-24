<?php

namespace Dvlpp\Metrics\Analyzers;

use Illuminate\Support\Collection;

class UrlAnalyzer extends Analyzer
{

    public function compile(Collection $visits)
    {
        $stack = [];

        foreach($visits as $visit) {
            
            $url = $visit->getUrl();

            if(array_key_exists($url, $stack)) {
                $stack[$url]++;
            }
            else {
                $stack[$url] = 1;
            }
        }

        return $stack;
    }

    public function consolidate(array $statistics)
    {
        $newStatistics = [];
        
        foreach ($statistics as $stat) {
            foreach($stat as $url => $count)
            {
                if(array_key_exists($url, $newStatistics)) {
                    $newStatistics[$url] = $newStatistics[$url] + $count;
                }      
                else {
                    $newStatistics[$url] = $count;
                }
            }
        }

        return $newStatistics;


    }


}