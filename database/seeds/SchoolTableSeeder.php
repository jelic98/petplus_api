<?php 
	
use Illuminate\Database\Seeder;

class SchoolTableSeeder extends Seeder {

	public function run() {
		DB::table('schools')->delete();

		DB::table('schools')->insert([
			['name' => 'Visoka poslovna skola', 'city' => 'Beograd', 'level' => 3],
			['name' => 'Gimnazija', 'city' => 'Krusevac', 'level' => 2],
			['name' => 'Tehnicka skola', 'city' => 'Novi Sad', 'level' => 2],
			['name' => 'Ekonomska skola', 'city' => 'Nis', 'level' => 2],
			['name' => 'OS Vuk Karadzic', 'city' => 'Kragujevac', 'level' => 1],
			['name' => 'Ekonomska skola', 'city' => 'Beograd', 'level' => 2],
			['name' => 'Gradjevinska skola', 'city' => 'Beograd', 'level' => 2],
			['name' => 'Pravni fakultet', 'city' => 'Kraljevo', 'level' => 4]
		]);
    }
}
?>
