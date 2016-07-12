<?php

namespace Dvlpp\Metrics\Contracts;

use Illuminate\Support\Collection;

interface ConsoliderInterface {

    public function consolidate(Collection $metrics);

}
