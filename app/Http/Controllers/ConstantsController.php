<?php 

namespace App\Http\Controllers;

use App\School;
use App\Subject;
use App\Level;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConstantsController extends Controller {

	public function schools() {
		return response()->json(School::with('level')->get());
	}

	public function addSchool(Request $request) {
		$this->validate($request, [ 
			'name' => 'required',
			'city' => 'required',
			'level' => 'required'
		]);

		if(School::where('name', $request['name'])
				 ->where('city', $request['city'])
				 ->where('level', $request['level'])
				 ->first()) {
			return MyResponse::show('School already exists', 400);
		}

		return response()->json(School::create($request->all()));
	}

	public function subjects() {
		return response()->json(Subject::all());	
	}
	
	public function addSubject(Request $request) {
		$this->validate($request, [
			'name' => 'required'
		]);

		if(Subject::where('name', $request['name'])
			      ->first()) {
			return MyResponse::show('Subject already exists', 400);
		}

		return response()->json(Subject::create($request->all()));
	}

	public function levels() {
		return response()->json(Level::all());	
	}
}
?>
