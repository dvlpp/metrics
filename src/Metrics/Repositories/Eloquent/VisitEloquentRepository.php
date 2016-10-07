<?php

namespace Dvlpp\Metrics\Repositories\Eloquent;

use Carbon\Carbon;
use Dvlpp\Metrics\TimeInterval;
use Dvlpp\Metrics\Visit;
use Illuminate\Support\Collection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Dvlpp\Metrics\Repositories\VisitRepository;

class VisitEloquentRepository implements VisitRepository
{
    /**
     * @var VisitModel
     */
    protected $visit;

    /**
     * @var DatabaseManager
     */
    protected $database;

    public function __construct(DatabaseManager $database)
    {
        $this->visit = new VisitModel;
        $this->database = $database;
    }

    /**
     * Get all records
     * 
     * @return Collection
     */
    public function all()
    {
        return $this->convertCollection($this->visit->all());
    }

    /**
     * Get the oldest visit
     * 
     * @return Visit | null
     */
    public function first()
    {
        $visit = $this->visit->orderBy('date')->first();

        if($visit) {
            return $this->convertObject($visit);
        }
    }

    /**
     * Get the newest visit
     * 
     * @return Visit
     */
    public function last()
    {
        $visit = $this->visit->orderBy('date','desc')->first();

        if($visit) {
            return $this->convertObject($visit);
        }
    }

    /**
     * Return all the visits for a given user
     * 
     * @param  string $userId 
     * @return Collection 
     */
    public function visitsFromUser($userId)
    {
        $visits = $this->visit->orderBy('date', 'desc')->whereUserId($userId)->get();

        return $this->convertCollection($visits);
    }

    /**
     * Return the last visit instance for a given user
     * 
     * @param  string $userId
     * @return Visit | null
     */
    public function lastVisitFromUser($userId)
    {
        $visit = $this->visit->orderBy('date', 'desc')->whereUserId($userId)->first();

        if($visit) {
            return $this->convertObject($visit);
        }
    }

    /**
     * Update previous visit's cookies to another value. Useful
     * when a user has logged in and we want to track the visits
     * it has already made.
     * 
     * @param  string  $oldCookie 
     * @param  string  $newCookie 
     * @return boolean
     */
    public function translateCookie($oldCookie, $newCookie)
    {
        return $this->getQueryBuilder()->where('cookie', $oldCookie)->update(['cookie' => $newCookie]);
    }

    /**
     * Set the user id for previous visits of a given cookie
     * 
     * @param string $cookie 
     * @param string $userId 
     * @return boolean
     */
    public function setUserForCookie($cookie, $userId)
    {
        return $this->getQueryBuilder()->where('cookie', $cookie)->where('user_id', null)->update(['user_id' => $userId]);
    }

    /**
     * Get oldest visit for given cookie
     * 
     * @param  string  $cookie 
     * @return Visit|null
     */
    public function oldestVisitForCookie($cookie)
    {
        $visit = $this->visit->where('cookie', $cookie)->orderBy('date', 'asc')->first();
        
        if($visit) {
            return $this->convertObject($visit);
        }
    }

    /**
     * Return all visits for a given interval
     * 
     * @param  Carbon $start
     * @param  Carbon $end
     * @return Collection
     */
    public function getTimeInterval(Carbon $start, Carbon $end)
    {
        $visits = $this->visit->where('date', ">=", $start)->where('date', "<=", $end)->get();

        // TODO : we could chunk these results to optimize the memory usage a little.

        return $this->convertCollection($visits);
    }

    /**
     * Return all visits for a given interval
     * 
     * @param  TimeInterval $interval
     * @return Collection
     */
    public function getByTimeInterval(TimeInterval $interval)
    {
        $visits = $this->visit->where('date', ">=", $interval->start())->where('date', "<=", $interval->end())->get();

        // TODO : we could chunk these results to optimize the memory usage a little.

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

    /**
     * Return instance of query builder for the metrics_visits table
     * 
     * @return Builder
     */
    protected function getQueryBuilder()
    {
        return $this->database->table('metric_visits');
    }
}
