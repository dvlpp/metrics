<?php

namespace Dvlpp\Metrics\Repositories;

use Carbon\Carbon;
use Dvlpp\Metrics\Visit;
use Dvlpp\Metrics\TimeInterval;

interface VisitRepository {

    public function all();

    public function store(Visit $visit);

    public function getTimeInterval(Carbon $start, Carbon $end);

    public function first();

    public function last();

    public function visitsFromUser($userId);

    public function lastVisitFromUser($userId);

    public function translateCookie($oldCookie, $newCookie);
}
