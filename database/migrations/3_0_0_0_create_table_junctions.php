<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableJunctions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('junctions', function (Blueprint $table) {
    		$table->increments('id');
            $table->integer('user')->unsigned();
			$table->foreign('user')->references('id')->on('users');
			$table->integer('school')->unsigned()->nullable();
			$table->foreign('school')->references('id')->on('schools');
			$table->integer('subject')->unsigned()->nullable();
			$table->foreign('subject')->references('id')->on('subjects');
			$table->integer('level')->unsigned()->nullable();
			$table->foreign('level')->references('id')->on('levels');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('junctions');
    }
}
