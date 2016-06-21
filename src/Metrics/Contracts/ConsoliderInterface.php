<?php

namespace Dvlpp\Metrics\Analyzers;

use Illuminate\Support\Collection;

interface ConsoliderInterface {

    public function consolidate(Collection $visits);

}
