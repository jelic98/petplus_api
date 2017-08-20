<?php

$app->get('/', function() use ($app) {
	return view('home', [
		'version' => '1.0', 
		'year' => date('Y')
	]);
});

$app->group(['prefix' => 'api/v1'], function($app) {
	$app->get('schools', 'ConstantsController@schools');	
	$app->get('subjects', 'ConstantsController@subjects');	
	$app->get('levels', 'ConstantsController@levels');

	$app->post('schools', 'ConstantsController@addSchool');
	$app->post('subjects', 'ConstantsController@addSubject');

	$app->get('logins', 'LoginsController@all');

	$app->post('register', 'UsersController@register');	
	$app->post('login', 'UsersController@login');
	
	$app->group(['middleware' => 'auth'], function($app) {
		$app->put('account/image', 'UsersController@setImage');	
		$app->put('account', 'UsersController@update');
		$app->delete('account', 'UsersController@delete');	
		$app->post('logout', 'UsersController@logout');
	});

	$app->group(['prefix' => 'users', 'middleware' => 'auth'], function($app) {
		$app->get('all', 'UsersController@all');
		$app->get('{id}', 'UsersController@get');
		$app->get('{id}/reviews', 'UsersController@reviews');
		$app->get('{id}/image', 'UsersController@getImage');	
	});

	$app->group(['prefix' => 'sessions', 'middleware' => 'auth'], function($app) {
		$app->get('all', 'SessionsController@all');	
		$app->post('create', 'SessionsController@create');	
		$app->put('{id}', 'SessionsController@update');	
		$app->get('{id}', 'SessionsController@get');	
		$app->post('{id}/cancel', 'SessionsController@cancel');	
		$app->post('{id}/review', 'SessionsController@review');	
		$app->post('{id}/{status}', 'SessionsController@decide');	
		$app->post('{id}/edit/{status}', 'SessionsController@decideEdit');	
	});
});
?>
