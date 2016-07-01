<?php

namespace Stubs;

use Dvlpp\Metrics\Visit;

class AcmeProvider {

    public function process(Visit $visit)
    {
        $visit->setCustomValue('test', 'test');
    }

}
