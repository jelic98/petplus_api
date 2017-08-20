<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model {
	
	protected $fillable = [
		'lecture', 
		'timeBegin', 
		'timeEnd', 
		'date', 
		'location',
		'finished',
		'canceled',
		'accepted',
		'mentor',
		'student',
		'review',
		'subject',
		'level'
	]; 
	
	public function review() {
		return $this->belongsTo('App\Review', 'review');
	}

	public function mentor() {
		return $this->belongsTo('App\User', 'mentor');
	}
	
	public function student() {
		return $this->belongsTo('App\User', 'student');
	}
	
	public function level() {
		return $this->belongsTo('App\Level', 'level');
	}
	
	public function subject() {
		return $this->belongsTo('App\Subject', 'subject');
	}
}
?>
