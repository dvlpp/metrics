<?php

namespace Dvlpp\Metrics;

use Illuminate\Support\Collection;

class Compiler
{
    /**
     * @var array
     */
    protected $analyzers;

    /**
     * @var string
     */
    protected $type;

    public function __construct(array $analyzers, $type)
    {
        $this->analyzers = $analyzers;
        $this->type = $type;
    }

    /**
     * Compile statistics from a collection of visit objects
     * 
     * @param  Collection $visits 
     * @return array
     */
    public function compile(Collection $visits)
    {
        $statistics = [];

        foreach($this->analyzers as $analyzer) {
            if($analyzer->getPeriod() == $this->type) {
                $statistics[get_class($analyzer)] = $analyzer->compile($visits);
            }
        }

        return $statistics;
    }

}