<?php

namespace Dvlpp\Metrics\Tools;

use Illuminate\Database\Migrations\Migration as BaseMigration;

abstract class Migration extends BaseMigration
{
    public function getConnection()
    {
        return config('metrics.connection');
    }
}
