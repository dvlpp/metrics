<?php

use Illuminate\Database\Schema\Blueprint;
use Dvlpp\Metrics\Tools\Migration;

class ChangeStatisticsToMediumText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('metric_metrics', function(Blueprint $table) {
            $table->string('statistics', 16777215)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('metric_metrics', function(Blueprint $table) {
            $table->text('statistics')->change();
        });
    }
}
