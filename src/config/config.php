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
    
];
