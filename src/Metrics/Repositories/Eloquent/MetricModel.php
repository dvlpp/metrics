<?php

namespace Dvlpp\Metrics\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;

class MetricModel extends Model
{
    use PreserveDateAsCarbonTrait;
    
    public $timestamps = false;

    protected $dates = ['start', 'end'];

    protected $table='metric_metrics';
    
}
