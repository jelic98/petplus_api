<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class MyResponse {

	public static function show($message = 'Error has occured', $code = 400) {
    	if(is_object($message)) { 
			$message = $message->toArray();
	 	}

	    $data = [
            'code' => $code,
            'message' => $message
        ];

    	return new JsonResponse($data, 200, [], 0);
	}	
}
?>
