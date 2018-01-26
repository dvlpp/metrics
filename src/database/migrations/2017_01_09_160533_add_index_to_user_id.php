<?php

use Illuminate\Database\Schema\Blueprint;
use Dvlpp\Metrics\Tools\Migration;

class AddIndexToUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('metric_visits', function(Blueprint $table) {
            $table->index('user_id');
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
            $table->dropIndex('user_id');
        });
    }
}
