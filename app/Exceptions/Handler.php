<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e) {
 		if($request->wantsJson() && !($e instanceof ValidationException)) {
			if(is_object($e)) {
				$e = substr(strrchr(get_class($e), "\\"), 1);
			}

    		return response()->json([
				'code' => method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500, 
				'message' => empty($e) ? 'Server error' : $e 
			]);
		}

 		return parent::render($request, $e);
	} 
}
