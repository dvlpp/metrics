<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->datetime('starts');
            $table->datetime('ends');
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
        Schema::drop('metrics_metrics', function(Blueprint $table) {
            Schema::drop('metric_metrics');
        });
    }
}
