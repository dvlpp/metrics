<?php

namespace Dvlpp\Metrics\Actions;

use Dvlpp\Metrics\Action;

class UserLoginAction extends Action {
    
    public $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

}
