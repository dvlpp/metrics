<?php

namespace Dvlpp\Metrics\Contracts;

use Illuminate\Support\Collection;

interface AnalyzerInterface {

    public function compile(Collection $visits);

}
