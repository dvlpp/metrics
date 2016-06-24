<?php

namespace Dvlpp\Metrics\Analyzers;

use Illuminate\Support\Collection;

class UserAgentAnalyzer extends Analyzer
{

    public function compile(Collection $visits)
    {
        $data = [];

        // The method will return its computed statistics
        // as an associative array.
        return $data;

    }
    
    public function consolidate(array $statistics)
    {
        $data = [];
        return $data;
    }


}