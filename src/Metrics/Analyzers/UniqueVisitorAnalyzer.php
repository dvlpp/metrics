<?php

namespace Dvlpp\Metrics\Analyzers;

use Illuminate\Support\Collection;

class UniqueVisitorAnalyzer extends Analyzer
{

    public function compile(Collection $visits)
    {

        $data = [];

        // The method will return its computed statistics
        // as an associative array.
        return $data;

    }

    // This operation will add two array returned by the compile() method
    // then return a consolidated array. 
    public function consolidate(array $statistics)
    {
        $data = [];
        return $data;
    }


}