<?php

namespace Dvlpp\Metrics\Contracts;

use Illuminate\Support\Collection;
use Dvlpp\Metrics\TimeInterval;

interface ConsoliderInterface {

    public function consolidate(Collection $metrics, TimeInterval $interval);

}
