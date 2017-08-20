<?php 

namespace App\Http\Controllers;

use Auth;
use App\School;
use App\Review;
use App\Level;
use App\Subject;
use App\User;
use App\Login;
use App\Session;
use App\Junction;
use App\Interval;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Hashing\BcryptHashet;

class UsersController extends Controller {
   
	public function register(Request $request) {
		$fields = [
			'email' => 'required',
			'password' => 'required',
			'firstName' => 'required',
			'lastName' => 'required',
			'type' => 'required'
		];

		$this->validate($request, $fields);

		if(User::where('email', $request['email'])->first()) {
			return MyResponse::show('Email is already in use', 400);
		}

		if($request['type'] == 'mentor') {
			$fields = array_merge($fields, [
				'interval' => 'required',
				'price' => 'required',
				'location' => 'required',
				'promoted' => 'required',
				'schools' => 'required',
				'subjects' => 'required',
				'levels' => 'required'
			]);
		}else if($request['type'] == 'mentor') {
			$fields = array_merge($fields, [
				'school' => 'required',
				'grade' => 'required'
			]);
		}else {
			return MyResponse::show('Invalid type', 400);	
		}

		$this->validate($request, $fields);
	
		$temp = [];

		if($request['type'] == 'mentor') {	
			if(sizeof($request['interval']) != 7) {
				return MyResponse::show('Interval must contain whole week', 400);
			}

			$model = new Interval();

			$days = $model->getColumns();

			$count = 0;
	
			foreach($request['interval'] as $day => $i) {
				$count++;
				
				if($day != $days[$count]) {
					return MyResponse::show('Invalid day specified in interval', 400);
				}

				$timeBegin = substr($i, 0, strpos($i, ','));
				$timeEnd = substr($i, strpos($i, ',') + 1);

				if(!preg_match('/(2[0-3]|[01][0-9]):([0-5][0-9])/', $timeBegin)
					|| !preg_match('/(2[0-3]|[01][0-9]):([0-5][0-9])/', $timeEnd)) {
					return myresponse::show('Invalid time format', 400);	
				}

				$timeBegin = strtotime($timeBegin);
				$timeEnd = strtotime($timeEnd);

				if($timeBegin > $timeEnd) {
					return MyResponse::show('Starting time must be before ending time', 400);
				}
			}
			
			$interval = Interval::create($request['interval']);
			$request['interval'] = $interval->id;
	
			$columns = [
				'school',
				'subject',
				'level'
			];

			foreach($columns as $column) {
				$temp[$column] = explode(',', $request[$column . 's']);
				unset($request[$column . 's']);
			}
		}else {
			$temp['school'] = $request['school'];
			unset($request['school']);
		}
				
		$create = [];

		foreach($fields as $key => $value) {
			$create[$key] = $request[$key];
		}

		$create['password'] = $this->hashPassword($request['password']);
		$create['accessToken'] = $this->createToken();

		$user = User::create($create);
		
		if($request['type'] == 'mentor') {
			foreach($temp as $key => $value) {
				switch($key) {
					case 'school':
						$model = new School();
						break;
					case 'subject':
						$model = new Subject();
						break;
					case 'level';
						$model = new Level();
						break;
				}

				foreach($value as $entry) {
					if(Junction::where('user', $user->id)
						       ->where($key, $entry)
							   ->first()) {
						continue;	
					}
						
					if(!$model->where('id', $entry)->first()) {
						return MyResponse::show('Invalid ' . $key, 400);	
					}

					Junction::create([
						'user' => $user->id,
						$key => $entry
					]);
				}
			}
		}else {
			if(isset($request['school']) 
				&& !Junction::where('user', $user->id)
						       ->where('school', $request['school'])
							   ->first()) {

				if(School::where('id', $request['school'])->first()) {
					return MyResponse::show('Invalid school', 400);	
				}

				Junction::create([
					'user' => $user->id,
					'school' => $temp['school']
				]);
			}
		}

		return response()->json(User::find($user->id));
	} 

	public function login(Request $request) {
		$this->validate($request, [
			'email' => 'required',
			'password' => 'required',
			'token' => 'required'
		]);

		$user = User::where('email', $request['email'])
					->firstOrFail();

		if(!app('hash')->check($request['password'], $user->password)) {
			return MyResponse::show('Incorrect password', 400);		
		}

		if($user->active == 1) {
			return MyResponse::show('User is already active', 400);
		}

		$accessToken = $this->createToken();

		User::where('id', $user->id)
		    ->update([
				'accessToken' => $accessToken,
				'fcmToken' => $request['token'],
				'active' => 1
			]);

		$login = Login::create([
			'user' => $user->id,
			'ip' => $request->ip()
		]);

		return response()->json([
			'accessToken' => $accessToken,
			'login' => $login
		]);	
	}

	public function logout() {
		$user = UsersController::currentUser();

		if($user->active == 0) {
			return MyResponse::show('User must be active', 400);
		}

		$user->active = 0;
	
		return MyResponse::show('Success', 200);
	}
	
	public function update(Request $request) {
		$user = UsersController::currentUser();
		
		$fields = [
			'firstName',
			'lastName'
		];

		if($user->type == 'mentor') {
			$fields = array_merge($fields, [
				'interval',
				'price',
				'location',
				'promoted'
			]);

			$columns = [
				'school',
				'subject',
				'level'
			];

			foreach($columns as $column) {
				$key = $column . 's';

				if(isset($request[$key])) {
					if(empty($request[$key])) {
						return MyResponse::show($key . ' are required', 400);
					}

					$temp[$column] = explode(',', $request[$key]);
					
					Junction::where('user', $user->id)
							->whereNotNull($column)
							->delete();
				}
			}
			
			foreach($temp as $key => $value) {
				$model = null;
				
				switch($key) {
					case 'school':
						$model = new School();
						break;
					case 'subject':
						$model = new Subject();
						break;
					case 'level';
						$model = new Level();
						break;
				}

				foreach($value as $entry) {
					if(!$model->where('id', $entry)->first()) {
						return MyResponse::show('Invalid ' . $key, 400);	
					}

					Junction::create([
						'user' => $user->id,
						$key => $entry
					]);
				}
			}
		}else {
			if(isset($request['school'])) {
				if(empty($request['school'])) {
					return MyResponse::show('School is required', 400);
				}

				Junction::where('user', $user->id)
						->whereNotNull('school')
						->delete();

				if(!School::where('id', $request['school'])->first()) {
					return MyResponse::show('Invalid school', 400);	
				}

				Junction::create([
					'user' => $user->id,
					'school' => $request['school']
				]);
			}

			$fields = array_merge($fields, [
				'grade'
			]);
		}
				
		$edit = [];

		foreach($fields as $field) {
			$new = $request[$field];

			if(empty($new)) {
				continue;	
			}

			$edit[$field] = $new;
		}

		$user->update($edit);
	
		return response()->json($user);
	}

	public function setImage(Request $request) {	
		$user = UsersController::currentUser();

		$this->validate($request, ['file' => 'required']);
	
		$file = $request->file('file');

		$name = $user->id . '.' . $file->getClientOriginalExtension();

		$path = 'images/profile';

		if(!File::exists($path)) {
			File::makeDirectory($path);	
		}

		$file->move($path, $name);

		$user->image = $name;

		return response()->json($user);
	}

	public function getImage($id) {
		return response()->file(User::findOrFail($id)->image);
	}

	public function delete() {
		$user = UsersController::currentUser();
		
		$user->active = 0;
		$user->delete();

		return MyResponse::show('Success', 200);
	}

	public function get($id) {
		$view = true;

		$user = User::findOrfail($id);
			
		if($user->id == UsersController::currentUser()->id) {
			$view = false;
		}

		foreach($user as $key => $value) {
			if(!isset($value) || empty($value)) {
				unset($user[$key]);	
			}
		}

		if($user->type == 'mentor') {
			$user['schools'] = Junction::with('school')
								       ->where('user', $id)
								       ->whereNotNull('school')
								       ->get();

			$user['subjects'] = Junction::with('subject')
		                                ->where('user', $id)
								        ->whereNotNull('subject')
								        ->get();
	
			$user['levels'] = Junction::with('level')
									   ->where('user', $id)
									   ->whereNotNull('level')
									   ->get();

			$user['sessions'] = Session::where('mentor', $user->id)->count();
			
			$user['students'] = sizeof(Session::where('mentor', $user->id)->groupBy('student')->get());
					
			$user['reviews'] = Session::where('mentor', $user->id)
			                          ->whereNotNull('review')
									  ->count();

			$user['rating'] = $this->reviews($user->id, true);

			if($view) {
				$user->increment('views');	
			}
		}else {
			$user['school'] = Junction::with('school')
								      ->where('user', $id)
								      ->whereNotNull('school')
								      ->first();
		}

		return response()->json($user);
	}
	
	public function all() {
		return response()->json(User::all());	
	}

	public function reviews($id, $calculate = false) {
		$user = User::findOrFail($id);

		if($user->type != 'mentor') {
			return MyResponse::show('Only mentors have reviews', 400);
		}

		$sessions = Session::where($user->type, $id)->get();
		
		$reviews = [];

		$sum = 0;
		$cnt = 0;

		for($i = 0; $i < sizeof($sessions); $i++) {
			$r = Review::find($sessions[$i]->review);
			
			if($r != null) {
				$reviews[$i] = $r;
				$sum += $reviews[$i]->rating;
				$cnt++;
			}
		}	

		if($calculate) {
			$avg = 0;

			if($cnt > 0) {
				$avg = $sum / $cnt;
			}

			return $avg;
		}

		return response()->json($reviews);
	}

	private function hashPassword($password) {
		return app('hash')->make($password);
	}

	private function createToken() {
		do {
			$token = str_random(64);
		}while(User::where("accessToken", $token)->first() instanceof User);
		
		return $token;
	}
	
	public static function currentUser() {
		$user = User::findOrFail(Auth::user()->id);

		return $user;
	}
}
?>
