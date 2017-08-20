<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSessions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->increments('id');
			$table->string('lecture', 50);
			$table->smallInteger('price')->default(0);;
			$table->string('location');
			$table->string('locationEdit')->nullable();
			$table->time('timeBegin', 11);	
			$table->time('timeEnd', 11);	
			$table->time('timeBeginEdit')->nullable();	
			$table->time('timeEndEdit')->nullable();	
			$table->date('date');	
			$table->date('dateEdit')->nullable();	
			$table->enum('status', ['accepted', 'declined'])->nullable();
			$table->boolean('canceled')->default(0);	
            $table->integer('mentor')->unsigned();
			$table->foreign('mentor')->references('id')->on('users');
            $table->integer('student')->unsigned();
			$table->foreign('student')->references('id')->on('users');
			$table->integer('subject')->unsigned()->nullable();
			$table->foreign('subject')->references('id')->on('subjects');
            $table->integer('level')->unsigned()->nullable();
			$table->foreign('level')->references('id')->on('levels');
			$table->integer('review')->unsigned()->nullable();
			$table->foreign('review')->references('id')->on('reviews');
			$table->timestamps();
        });    
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sessions');
    }
}
