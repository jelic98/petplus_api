<?php 

namespace App\Http\Controllers;

use App\Session;
use App\Review;
use App\User;
use App\Junction;
use App\Interval;
use App\School;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class SessionsController extends Controller {
    
	public function create(Request $request) {
		$student = UsersController::currentUser();

		if($student->type != 'student') {
			return MyResponse::show('Only student can create session', 400);
		}

		$fields = [
			'lecture' => 'required',
			'date' => 'required',
			'time' => 'required',
			'location' => 'required',
			'mentor' => 'required',
			'subject' => 'required'
		];

		$this->validate($request, $fields);

		foreach($fields as $key => $value) {
			$fields[$key] = $request[$key];
		}

		$fields['student'] = $student->id;

		$school = Junction::where('user', $fields['student'])->first()->school; 
		$fields['level'] = School::findOrFail($school)->level; 
		
		if(strtotime($fields['date']) <= strtotime('now')) {
			return MyResponse::show('Date must be in future', 400);
		}
	
		$day = strtolower(date('D', strtotime($fields['date'])));

		$mentor = User::where('type', 'mentor')->findOrFail($fields['mentor']);

		$interval = Interval::findOrFail($mentor->interval)[$day];

		$intervalBegin = strtotime(substr($interval, 0, strpos($interval, ',')));	
		$intervalEnd = strtotime(substr($interval, strpos($interval, ',') + 1));

		$time = $fields['time'];
		unset($fields['time']);

		$sTimeBegin = substr($time, 0, strpos($time, ','));
		$sTimeEnd = substr($time, strpos($time, ',') + 1);

		if(!preg_match('/(2[0-3]|[01][0-9]):([0-5][0-9])/', $sTimeBegin)
			|| !preg_match('/(2[0-3]|[01][0-9]):([0-5][0-9])/', $sTimeEnd)) {
			return myresponse::show('Invalid time format', 400);	
		}

		$fields['timeBegin'] = $sTimeBegin;
		$fields['timeEnd'] = $sTimeEnd;

		$timeBegin = strtotime($sTimeBegin);
		$timeEnd = strtotime($sTimeEnd);

		if($timeBegin > $timeEnd) {
			return MyResponse::show('Starting time must be before ending time', 400);
		}
	
		$duration = ceil(abs($timeEnd - $timeBegin) / 3600);

		if($duration < 1) {
			return MyResponse::show('Session duration minimum is one hour', 400);
		}

		if($timeBegin < $intervalBegin
			|| $timeBegin > $intervalEnd
			|| $timeEnd > $intervalEnd) {
			return MyResponse::show('Mentor is not available in specified time', 400);	
		}

		$fields['price'] = $mentor->price * $duration;

		if(Session::where('lecture', $fields['lecture'])
			     	  	 	->where('timeBegin', $fields['timeBegin'])
			     	  	 	->where('timeEnd', $fields['timeEnd'])
			     	   		->where('date', $fields['date'])
			     	   		->where('location', $fields['location'])
			     	   		->where('mentor', $fields['mentor'])
			     	   		->where('subject', $fields['subject'])
			     	   		->first()) {
			return MyResponse::show('Session already exists', 400);
		}

		$name = $student->firstName . ' ' . $student->lastName;

		$session = Session::create($fields);	
		
		$this->notify('New session', $name . ' has created a session.', ['session' => $session->id], $mentor->fcmToken);			

		return response()->json($session);
	} 
		
	public function update(Request $request, $id) {	
		$session = Session::findOrFail($id);

		if($session->status != 'accepted') {
			return MyResponse::show('Session must be accepted', 400);
		}

		if(!is_null($session->dateEdit)
			|| !is_null($session->timeBeginEdit)
			|| !is_null($session->timeEndEdit)
			|| !is_null($session->locationEdit)) {
			return MyResponse::show('Previous update is not reviewed', 400);
		}

		$time = $request['time'];
		
		$timeBegin = substr($time, 0, strpos($time, ','));
		$timeEnd = substr($time, strpos($time, ',') + 1);

		if(isset($request['time']) && (!preg_match('/(2[0-3]|[01][0-9]):([0-5][0-9])/', $timeBegin)
			|| !preg_match('/(2[0-3]|[01][0-9]):([0-5][0-9])/', $timeEnd))) {
			return myresponse::show('Invalid time format', 400);	
		}

		unset($request['time']);

		$request['timeBegin'] = $timeBegin . ':00';
		$request['timeEnd'] = $timeEnd . ':00';

		$fields = [
			'date',
			'timeBegin',
			'timeEnd',
			'location'
		];

		$old = [
			$session->date,
			$session->timeBegin,
			$session->timeEnd,
			$session->location
		];

		$edit = [];

		for($i = 0; $i < sizeof($fields); $i++) {
			$new = $request[$fields[$i]];
			
			if(!empty($new)) {
				$edit[$fields[$i] . 'Edit'] = $new;
			}else {
				$edit[$fields[$i] . 'Edit'] = $old;
			}
		}
		
		$currentUser = UsersController::currentUser();

		if($currentUser->type != 'mentor') {
			return MyResponse::show('Only mentor can update session', 400);
		}
	
		if($currentUser->id != $session->mentor) {
			return MyResponse::show('Mentor not authorized', 400);
		}

		$user = User::findOrFail($session->student);

		$name = $currentUser->firstName . ' ' . $currentUser->lastName;
	
		Session::where('id', $id)->update($edit);

		$this->notify('Updated session', $name . ' has changed session details.', ['session' => $id], $user->fcmToken);				
	
		return response()->json(Session::find($id));
	}

	public function decideEdit($id, $status) {
		if($status != 'accept' && $status != 'decline') {
			return MyResponse::show('Invalid action', 400);
		}

		$session = Session::findOrFail($id);
	
		if($session->status != 'accepted') {
			return MyResponse::show('Session must be accepted', 400);
		}

		if(is_null($session->dateEdit)
			&& is_null($session->timeBeginEdit)
			&& is_null($session->timeEndEdit)
			&& is_null($session->locationEdit)) {
			return MyResponse::show('Session is not updated', 400);
		}

		$currentUser = UsersController::currentUser();

		if($currentUser->type != 'student') {
			return MyResponse::show('Only student can review session update', 400);
		}

		if($currentUser->id != $session->student) {
			return MyResponse::show('Student not authorized', 400);
		}

		$user = User::findOrFail($session->mentor);

		$name = $currentUser->firstName . ' ' . $currentUser->lastName;

		if($status == 'accept') {
			Session::where('id', $id)->update([
				'date' => $session->dateEdit, 
				'timeBegin' => $session->timeBeginEdit, 
				'timeEnd' => $session->timeEndEdit, 
				'location' => $session->locationEdit, 
			]);

			$this->notify('Accepted changes', $name . ' has accepted session changes.', ['session' => $id], $user->fcmToken);				
		}else {	
			$this->notify('Declined changes', $name . ' has declined session changes.', ['session' => $id], $user->fcmToken);				
		}
		
		Session::where('id', $id)->update([
			'dateEdit' => null,
			'timeBeginEdit' => null,
			'timeEndEdit' => null,
			'locationEdit' => null
		]);
	
		return MyResponse::show('Success', 200);
	}

	public function decide($id, $status) {
		if($status != 'accept' && $status != 'decline') {
			return MyResponse::show('Invalid action', 400);
		}
		
		$session = Session::findOrFail($id);
	
		if($status == 'accept') {
			if($session->status == 'declined') {
				return MyResponse::show('Session cannot be declined', 400);
			}else if($session->status == 'accepted') {
				return MyResponse::show('Session is already accepted', 400);
			}
		}else {
			if($session->status == 'accepted') {
				return MyResponse::show('Session cannot be accepted', 400);
			}else if($session-status == 'declined') {
				return MyResponse::show('Session is already declined', 400);
			}	
		}
	
		$currentUser = UsersController::currentUser();

		if($currentUser->type != 'mentor') {
			return MyResponse::show('Only mentor can ' . $status . ' session', 400);
		}

		if($currentUser->id != $session->mentor) {
			return MyResponse::show('Mentor not authorized', 400);	
		}
	
		$name = $currentUser->firstName . ' ' . $currentUser->lastName;
		
		$user = User::findOrFail($session->student);
		
		if($status == 'accept') {
			Session::where('id', $id)->update(['status' => 'accepted']);
			
			$this->notify('Accepted session', $name . ' has accepted a session.', ['session' => $id], $user->fcmToken);			
		}else {
			Session::where('id', $id)->update(['status' => 'declined']);
		
			$this->notify('Declined session', $name . ' has declined a session.', ['session' => $id], $user->fcmToken);			
		}
	
		return MyResponse::show('Success', 200);
	}

	public function cancel($id) {
		$session = Session::findOrFail($id);

		if($session->status != 'accepted') {
			return MyResponse::show('Session must be accepted', 400);
		}

		if($session->canceled == 1) {
			return MyResponse::show('Session is already canceled', 400);
		}

		$currentUser = UsersController::currentUser();

		$user = User::findOrFail($session->student);

		if($currentUser->type == 'student') {
			$user = User::findOrFail($session->mentor);	
		}
	
		if($currentUser->id != $session->student
			&& $currentUser->id != $session->mentor) {
			return MyResponse::show('User not authorized', 400);
		}

		$name = $currentUser->firstName . ' ' . $currentUser->lastName;
		
		Session::where('id', $id)->update(['canceled' => 1]);
		
		$this->notify('Canceled session', $name . ' has canceled a session.', ['session' => $id], $user->fcmToken);			

		return MyResponse::show('Success', 200);
	}

	public function review(Request $request, $id) {
		$this->validate($request, [
			'rating' => 'required',
			'description' => 'required'
		]);

		$session = Session::findOrFail($id);	
		
		$currentUser = UsersController::currentUser();

		if($currentUser->type != 'student') {
			return MyResponse::show('Only student can review session', 400);
		}

		if($currentUser->id != $session->student) {
			return MyResponse::show('Student not authorized', 400);
		}

		if($session->status != 'accepted') {
			return MyResponse::show('Session must be accepted', 400);
		}
	
		if($session->canceled == 1) {
			return MyResponse::show('Session must not be canceled', 400);
		}

		if(isset($session->review)) {
			return MyResponse::show('Session already has a review', 400);
		}

		if($request['rating'] < 1 || $request['rating'] > 5) {
			return MyResponse::show('Rating is out of bounds', 400);	
		}

		$review = Review::create($request->all());

		Session::where('id', $id)->update(['review' => $review->id]);
		
		return response()->json($review);
	}

	public function get($id) {
		$session = $this->getSession($id);
		$user = UsersController::currentUser();

		if($session->student != $user->id && $session->mentor != $user->id) {
			return MyResponse::show('User not authorized', 400);
		}

		return response()->json($session);
	}

	public function all() {
		$user = UsersController::currentUser();

		return response()->json(Session::where($user->type, $user->id)->get());	
	}

	private function getSession($id) {
		return Session::with('student', 'mentor', 'review', 'level', 'subject')->findOrFail($id);
	}

	private function notify($title, $body, $data, $token) {
		//todo uncomment before release
		return;

		$optionBuilder = new OptionsBuilder();
		$optionBuilder->setTimeToLive(60*20);

		$notificationBuilder = new PayloadNotificationBuilder($title);
		$notificationBuilder->setBody($body)
				            ->setSound('default');
				    
		$dataBuilder = new PayloadDataBuilder();
		$dataBuilder->addData($data);

		$option = $optionBuilder->build();
		$notification = $notificationBuilder->build();
		$data = $dataBuilder->build();

		$downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

		$downstreamResponse->numberSuccess();
		$downstreamResponse->numberFailure();
		$downstreamResponse->numberModification();

		$downstreamResponse->tokensToDelete(); 
		$downstreamResponse->tokensToModify(); 
		$downstreamResponse->tokensToRetry();	
	}
}
?>
