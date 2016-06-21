<?php

namespace Dvlpp\Metrics\Repositories\Eloquent;

use Dvlpp\Metrics\TimeInterval;
use Dvlpp\Metrics\Visit;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Dvlpp\Metrics\Repositories\VisitRepository;

class VisitEloquentRepository implements VisitRepository
{
    protected $visit;

    public function __construct()
    {
        $this->visit = new VisitModel;
    }

    public function all()
    {
        return $this->convertCollection($this->visit->all());
    }

    public function first()
    {
        return $this->convertObject($this->visit->orderBy('date')->first());
    }

    public function last()
    {
        return $this->convertObject($this->visit->orderBy('date','desc')->first());
    }

    /**
     * Return all visits for a given interval
     * 
     * @param  TimeInterval $interval
     * @return Collection
     */
    public function getTimeInterval(TimeInterval $interval)
    {
        $visits = $this->visit->where('date', ">=", $interval->start())->where('date', "<=", $interval->end())->get();

        return $this->convertCollection($visits);
    }

    /**
     * Store in database 
     * 
     * @param  Visit  $visit
     * @return void
     */
    public function store(Visit $visit)
    {
        $attributes = $visit->toArray();

        if(isset($attributes['id']) && $attributes['id'] !== null) {
            return $this->saveExisting($attributes);
        }
        else {
            unset($attributes['id']);
        }

        $visit = VisitModel::create($attributes);
    }

    /**
     * Store an existing record
     * 
     * @param  array  $attributes 
     * @return 
     */
    protected function saveExisting(array $attributes)
    {
        $id = $attributes['id'];
        $visitModel = Visit::find($id);
        foreach($attributes as $key => $value) {
            $visitModel->$key = $value;
        }
        $visitModel->save();
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
            return Visit::createFromArray($item->toArray() );
        });
    }

    protected function convertObject(VisitModel $visit)
    {
        return Visit::createFromArray($visit->toArray() );
    }
}
