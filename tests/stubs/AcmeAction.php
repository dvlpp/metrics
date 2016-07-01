<?php

namespace Stubs;

use Dvlpp\Metrics\Action;

class AcmeAction extends Action {

    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
