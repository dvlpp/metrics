<?php

namespace Dvlpp\Metrics;

use Illuminate\Http\Request;

class Visit {

    /**
     * Or user id ?
     * @var [type]
     */
    protected $user;

    /**
     * Custom data to be added 
     * 
     * @var array
     */
    protected $customData = [];


    public function __construct(Request $request)
    {
        $this->initFromRequest($request);
    }

    protected function initFromRequest(Request $request)
    {
        
    }




}