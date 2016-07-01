<?php

namespace Dvlpp\Metrics\Actions;

use Dvlpp\Metrics\Action;

class UserLogoutAction extends Action {
    
    public $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

}
