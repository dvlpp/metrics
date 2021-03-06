<?php

use Illuminate\Database\Schema\Blueprint;
use Dvlpp\Metrics\Tools\Migration;

class CreateMetricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metric_metrics', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('type')->unsigned();
            $table->datetime('start');
            $table->datetime('end');
            $table->bigInteger('count')->unsigned();
            $table->text('statistics');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('metric_metrics');
    }
}
