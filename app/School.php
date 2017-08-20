<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class School extends Model {

	public $timestamps = false;

	protected $fillable = [
		'name',
		'city',
		'level'
	]; 	
	
	public function level() {
		return $this->belongsTo('App\Level', 'level');
	}
}
?>
