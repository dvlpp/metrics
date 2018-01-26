<?php

use Illuminate\Database\Schema\Blueprint;
use Dvlpp\Metrics\Tools\Migration;

class AddIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('metric_visits', function(Blueprint $table) {
            $table->index('date');
            $table->index('session_id');
            $table->index('cookie');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('metric_visits', function(Blueprint $table) {
            $table->dropIndex('date');
            $table->dropIndex('session_id');
            $table->dropIndex('cookie');
        });
    }
}
