<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
			$table->enum('type', ['student', 'mentor']);
			$table->string('firstName', 50);
			$table->string('lastName', 50);
			$table->string('email')->unique();
			$table->string('password', 60);
			$table->string('accessToken', 64)->unique();
			$table->text('image')->nullable();
			$table->string('fcmToken')->nullable();
			$table->boolean('active')->default(0);
			$table->integer('views')->default(0);
	
			$table->integer('interval')->unsigned()->nullable();
			$table->foreign('interval')->references('id')->on('intervals');
			$table->string('location')->nullable();
			$table->smallInteger('price')->nullable();
			$table->boolean('promoted')->default(0);
			
			$table->smallInteger('grade')->nullable();
			
			$table->softDeletes();
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
        Schema::drop('users');
    }
}
