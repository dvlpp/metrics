<?php

namespace Dvlpp\Metrics\Contracts;

use Illuminate\Support\Collection;
use Dvlpp\Metrics\TimeInterval;

interface AnalyzerInterface {

    public function compile(Collection $visits, TimeInterval $interval);

}
