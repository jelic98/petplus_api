<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Login extends Model {
	
	protected $fillable = [
		'ip', 
		'user'
	]; 

	public function user() {
		return $this->belongsTo('App\User', 'user');	
	}
}
?>
