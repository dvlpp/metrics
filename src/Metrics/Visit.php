<?php

namespace Dvlpp\Metrics;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;

class Visit implements Arrayable
{
    /**
     * id of the record in database
     * 
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $user_id;
   
    /**
     * IP of the visit
     * 
     * @var string
     */
    protected $ip;

    /**
     * Full user agent of the visit
     * 
     * @var string
     */
    protected $user_agent;

    /**
     * The tracking cookie, if set
     * 
     * @var string
     */
    protected $cookie;

    /**
     * The full url of the visit (without query string)
     * 
     * @var string
     */
    protected $url;

    /**
     * The referer for current visit
     * 
     * @var string
     */
    protected $referer;

    /**
     * Actions objets
     * 
     * @var Collection
     */
    protected $actions;

    /**
     * Custom data to be added 
     * 
     * @var array
     */
    protected $custom = [];

    /**
     * DateTime of the visit
     *
     * @var Carbon
     */
    protected $date;

    public function __construct()
    {
        $this->actions = new Collection;
    }

    /**
     * Create a Visit instance from a Request object
     * 
     * @param  Request $request
     * @return Visit
     */
    public static function createFromRequest(Request $request)
    {
        $visit = new Static;
        $visit->date = Carbon::now();
        $visit->url = $request->getUri();
        $visit->referer = $request->server('HTTP_REFERER');
        $visit->ip = $request->ip();
        if($request->hasCookie(config('metrics.cookie_name'))) {
            $visit->cookie = $request->cookies->get(config('metrics.cookie_name'));    
        }
        else {
            $visit->cookie = str_random(32);
        }
        $visit->user_agent = $request->server('HTTP_USER_AGENT');
        return $visit;
    }

    /**
     * Create a Visit instance from an array. We'll use this Essentially
     * to reconstruct a Visit object from a database row.
     * 
     * @param  array  $data
     * @return Visit
     */
    public static function createFromArray(array $data)
    {
        $visit = new Static;
        if (isset($data['id'])) {
            $visit->id = $data['id'];    
        }
        $visit->ip = $data['ip'];
        $visit->user_agent = $data['user_agent'];
        $visit->user_id = $data['user_id'];
        $visit->custom = $data['custom'];
        $visit->url = $data['url'];
        $visit->referer = $data['referer'];
        $visit->date = $data['date'];
        $visit->cookie = $data['cookie'];
        foreach($data['actions'] as $action) {
            $visit->addAction(unserialize($action));
        }
        return $visit;
    }

    /**
     * Set the user id for this record
     * 
     * @param  int $userId
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
    }

    /**
     * Get the user id
     *
     * @return string
     */
    public function userId()
    {
        return $this->user_id;
    }

    /**
     * Get the visited url
     * 
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get request IP
     * 
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Return laravel cookie
     * 
     * @return string
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * Return date
     * 
     * @return Carbon\Carbon
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Return user agent 
     * 
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Get actions on this visit
     * 
     * @return ActionCollection
     */
    public function actions()
    {
        return $this->actions;
    }

    /**
     * Attach an action to the visit
     * 
     * @param Action $action [description]
     * @return Visit
     */
    public function addAction(Action $action)
    {
        $this->actions->push($action);
        return $this;
    }

    /**
     * Get the action from the given class
     * 
     * @param  string $actionClass
     * @return  Action | null
     */
    public function getAction($actionClass)
    {
        foreach($this->actions as $action) {
            if(get_class($action) == $actionClass) {
                return $action;
            }
        }
        return null;
    }

    /**
     * Return true if the Visit has an action of the given class
     * 
     * @param  string  $actionClass
     * @return boolean        
     */
    public function hasAction($actionClass)
    {
        foreach($this->actions as $action) {
            if(get_class($action) == $actionClass) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add a custom tracking value to the object
     * 
     * @param string $key 
     * @param mixed $value
     */
    public function setCustomValue($key, $value)
    {   
        $this->custom[$key] = $value;
    }

    /**
     * Get a custom value from the object
     * 
     * @param  string $key
     * @return mixed
     */
    public function getCustomValue($key)
    {
        return $this->custom[$key];
    }

    /**
     * Check if a custom value exists
     * 
     * @param  string  $key 
     * @return boolean
     */
    public function hasCustomValue($key)
    {
        return array_key_exists($key, $this->custom);
    }

    /**
     * Convert object to array, including serialisation of actions
     * 
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'ip' => $this->ip,
            'user_id' => $this->user_id,
            'user_agent' =>  $this->user_agent,
            'actions' => $this->getSerializedActions(),
            'custom' => $this->custom,
            'cookie' => $this->cookie,
            'url' => $this->url,
            'referer' => $this->referer,
            'date' => $this->date,
        ];
    }

    /**
     * Get actions as serialized objects
     * 
     * @return array
     */
    protected function getSerializedActions()
    {
        $actions = [];

        foreach($this->actions as $action) {
            $actions[] = serialize($action);
        }

        return $actions;
    }

    /**
     * Magic getter for object's properties
     * 
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->$key;
    }
}
