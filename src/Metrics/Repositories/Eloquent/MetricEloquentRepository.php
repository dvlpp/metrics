<?php

namespace Dvlpp\Metrics\Repositories\Eloquent;

use Dvlpp\Metrics\Metric;
use Dvlpp\Metrics\TimeInterval;
use Dvlpp\Metrics\Repositories\MetricRepository;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class MetricEloquentRepository implements MetricRepository
{
    /**
     * @var MetricModel
     */
    protected $metric;

    public function __construct()
    {
        $this->metric = new MetricModel;
    }

    /**
     * Return all metric rows
     * 
     * @return Collection
     */
    public function all()
    {
        return $this->convertCollection($this->metric->all());
    }

    /**
     * Return the single object corresponding to the time interval
     *
     * @param  TimeInterval $interval
     * @return  Metric
     */
    public function find(TimeInterval $interval)
    {
        $metrics = $this->metric->where('start', $interval->start())->where('end', $interval->end())->first();
    }

    /**
     * Return all the metric records for the given time interval
     * 
     * @param  TimeInterval $interval
     * @return Collection
     */
    public function getTimeInterval(TimeInterval $interval)
    {
        $metrics = $this->metric->where('start', '>=', $interval->start())->where('end', '<', $interval->end())->get();
        
        return $this->convertCollection($metrics);
    }

    /**
     * Return true if a time interval exists
     * 
     * @param  TimeInterval $interval 
     * @return boolean             
     */
    public function hasTimeInterval(TimeInterval $interval)
    {
        $metric = $this->find($interval);
        
        return $metric ? true : false;
    }

    /**
     * Store a Metric object
     * @param  Metric $metric 
     * @return  void
     */
    public function store(Metric $metric)
    {
        $attributes = $metric->toArray();

        if(isset($attributes['id']) && $attributes['id'] !== null) {
            return $this->saveExisting($attributes);
        }
        else {
            unset($attributes['id']);
        }

        $metric = VisitModel::create($attributes);
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
