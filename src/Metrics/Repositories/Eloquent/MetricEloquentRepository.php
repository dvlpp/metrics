<?php

namespace Dvlpp\Metrics\Repositories\Eloquent;

use Dvlpp\Metrics\Metric;
use Dvlpp\Metrics\Repositories\MetricRepository;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class MetricEloquentRepository implements MetricRepository
{
    protected $metric;

    public function __construct()
    {
        $this->metric = new MetricModel;
    }

    public function all()
    {
        return $this->convertCollection($this->metric->all());
    }

    /**
     * Return the metric record for the given time interval
     * 
     * @param  Carbon $start
     * @param  Carbon $end  
     * @return Collection
     */
    public function getTimeInterval(TimeInterval $interval)
    {
        $metric = $this->metric->where('start', $interval->start())->where('end', $interval->end())->first();
        return $this->convertObject($metric);
    }

    public function hasTimeInterval(TimeInterval $interval)
    {

    }

    public function store(Metric $metric)
    {

    }

    /**
     * Convert an Eloquent Collection into a standard Collection of Visit objects
     * 
     * @return Collection
     */
    protected function convertCollection(EloquentCollection $collection)
    {
        $baseCollection = $collection->toBase();
        
        return $baseCollection->transform(function ($item, $key) {
            return Metric::createFromArray($item->toArray() );
        });
    }

    protected function toObject(MetricModel $model)
    {
        return Metric::createFromArray($model->toArray() );
    }
}
