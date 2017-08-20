<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Interval extends Model {

	public $timestamps = false;

	protected $fillable = [
		'monday',
		'tuesday',
		'wednesday',
		'thursday',
		'friday',
		'saturday',
		'sunday'
	]; 

	public function getColumns() {
		return $this->getConnection()
		            ->getSchemaBuilder()
					->getColumnListing($this->getTable());
	}
}
?>
