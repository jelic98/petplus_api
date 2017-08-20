<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableIntervals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intervals', function (Blueprint $table) {
            $table->increments('id');
 			$table->string('mon', 11)->default('00:00,23:59');
 			$table->string('tue', 11)->default('00:00,23:59');
 			$table->string('wed', 11)->default('00:00,23:59');
 			$table->string('thu', 11)->default('00:00,23:59');
 			$table->string('fri', 11)->default('00:00,23:59');
 			$table->string('sat', 11)->default('00:00,23:59');
 			$table->string('sun', 11)->default('00:00,23:59');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('intervals');
    }
}
