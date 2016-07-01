<?php 

return [
    
    /*
    |--------------------------------------------------------------------------
    | Cookie Name
    |--------------------------------------------------------------------------
    |
    | Metrics will use a cookie to track user's visits weither they are logged
    | in or not. 
    |
    */
   'cookie_name' => 'metrics_tracker',
    
    /*
    |--------------------------------------------------------------------------
    | Visits retention time
    |--------------------------------------------------------------------------
    |
    | This option tells the package how much time it preserves the visits in the 
    | database. It has to be greater or equal to the smallest analyzer period. 
    |
    */
    'visits_retention_time' => '1 month',
    
    /*
    |--------------------------------------------------------------------------
    | Analyzers & Consoliders
    |--------------------------------------------------------------------------
    |
    | Here you can fine tune which analyzers will be run and at which time interval  
    |
    */
   'analyzers' => [
        'hourly' => [
            Dvlpp\Metrics\Analyzers\UrlAnalyzer::class,
            Dvlpp\Metrics\Analyzers\UserAgentAnalyzer::class,
            Dvlpp\Metrics\Analyzers\UniqueVisitorAnalyzer::class,
        ],
        'daily' => [
            Dvlpp\Metrics\Analyzers\UniqueVisitorAnalyzer::class,
        ],
        'monthly' => [],
        'yearly' => [],
   ],

   'consoliders' => [
         
        'daily' => [
            Dvlpp\Metrics\Analyzers\UrlAnalyzer::class,
            Dvlpp\Metrics\Analyzers\UserAgentAnalyzer::class,
        ],
        'monthly' => [
            Dvlpp\Metrics\Analyzers\UniqueVisitorAnalyzer::class,
            Dvlpp\Metrics\Analyzers\UrlAnalyzer::class,
            Dvlpp\Metrics\Analyzers\UserAgentAnalyzer::class,
        ],
        'yearly' => [
            Dvlpp\Metrics\Analyzers\UniqueVisitorAnalyzer::class,
            Dvlpp\Metrics\Analyzers\UrlAnalyzer::class,
            Dvlpp\Metrics\Analyzers\UserAgentAnalyzer::class,
        ],
   ],


];
