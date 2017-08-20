<?php

namespace App\Providers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider {
    public function register() {}

    public function boot() {
    	Auth::viaRequest('api', function($request) {
			if($request->header('accessToken')) {
				return User::where('accessToken', $request->header('accessToken'))
						   ->where('active', 1)
						   ->first();
			}
		});
	}
}
