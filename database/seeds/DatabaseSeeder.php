<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    
	public function run() {
    	$this->call('LevelTableSeeder');
    	$this->call('SubjectTableSeeder');
    	$this->call('IntervalTableSeeder');
		$this->call('SchoolTableSeeder');
	}
}
