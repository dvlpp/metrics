<?php

namespace Dvlpp\Metrics\Actions;

use Dvlpp\Metrics\Action;

class UserLoginAction extends Action {
    
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

}
