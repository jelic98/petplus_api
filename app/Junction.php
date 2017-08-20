<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Junction extends Model {

	public $timestamps = false;

	protected $fillable = [
		'user',
		'school',
		'subject',
		'level'
	]; 	
	
	public function user() {
		return $this->belongsTo('App\User', 'user');
	}

	public function school() {
		return $this->belongsTo('App\School', 'school');
	}

	public function subject() {
		return $this->belongsTo('App\Subject', 'subject');
	}

	public function level() {
		return $this->belongsTo('App\Level', 'level');
	}
}
?>
