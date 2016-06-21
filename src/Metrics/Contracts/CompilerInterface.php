<?php

namespace Dvlpp\Metrics\Analyzers;

use Illuminate\Support\Collection;

interface CompilerInterface {

    public function compile(Collection $visits);

}
