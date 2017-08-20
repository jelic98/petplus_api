<?php 
	
use Illuminate\Database\Seeder;

class SubjectTableSeeder extends Seeder {

	public function run() {
		DB::table('subjects')->delete();

		DB::table('subjects')->insert([
			['name' => 'Matematika'],
			['name' => 'Fizika'],
			['name' => 'Srpski jezik'],
			['name' => 'Knjizevnost'],
			['name' => 'Osnove racunarskih sistema'],
			['name' => 'Svet oko nas'],
			['name' => 'Programiranje 1'],
			['name' => 'Hemija']
		]);
    }
}
?>
