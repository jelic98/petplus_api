<?php 
	
use Illuminate\Database\Seeder;

class LevelTableSeeder extends Seeder {

	public function run() {
		DB::table('levels')->delete();

		DB::table('levels')->insert([
			['name' => 'Osnovna skola'],
			['name' => 'Srednja skola'],
			['name' => 'Visa skola'],
			['name' => 'Fakultet']
		]);
    }
}
?>
