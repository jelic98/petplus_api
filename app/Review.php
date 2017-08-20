<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model {
	
	protected $fillable = [
		'rating',
		'description'
	]; 	
}
?>
