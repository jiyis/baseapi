<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');


$api->version('v1',['namespace' => 'App\Http\Controllers\Api\V1', 'middleware' => []], function ($api) {
    $api->group([
//        'limit' => '',
    ], function($api) {
        
		$api->get('users','UserController@index');
		$api->get('users/count','UserController@count');
		$api->get('users/{id}','UserController@show');
		$api->post('users','UserController@store');
		$api->patch('users/{id}','UserController@update');
		$api->delete('users/{id}','UserController@destroy');
		$api->get('users','UserController@index');
		$api->get('users/count','UserController@count');
		$api->get('users/{id}','UserController@show');
		$api->post('users','UserController@store');
		$api->patch('users/{id}','UserController@update');
		$api->delete('users/{id}','UserController@destroy');
		$api->get('users','UserController@index');
		$api->get('users/count','UserController@count');
		$api->get('users/{id}','UserController@show');
		$api->post('users','UserController@store');
		$api->patch('users/{id}','UserController@update');
		$api->delete('users/{id}','UserController@destroy');
		$api->get('users','UserController@index');
		$api->get('users/count','UserController@count');
		$api->get('users/{id}','UserController@show');
		$api->post('users','UserController@store');
		$api->patch('users/{id}','UserController@update');
		$api->delete('users/{id}','UserController@destroy');
		$api->get('users','UserController@index');
		$api->get('users/count','UserController@count');
		$api->get('users/{id}','UserController@show');
		$api->post('users','UserController@store');
		$api->patch('users/{id}','UserController@update');
		$api->delete('users/{id}','UserController@destroy');
		$api->get('users','UserController@index');
		$api->get('users/count','UserController@count');
		$api->get('users/{id}','UserController@show');
		$api->post('users','UserController@store');
		$api->patch('users/{id}','UserController@update');
		$api->delete('users/{id}','UserController@destroy');
		$api->get('users','UserController@index');
		$api->get('users/count','UserController@count');
		$api->get('users/{id}','UserController@show');
		$api->post('users','UserController@store');
		$api->patch('users/{id}','UserController@update');
		$api->delete('users/{id}','UserController@destroy');
		$api->get('users','UserController@index');
		$api->get('users/count','UserController@count');
		$api->get('users/{id}','UserController@show');
		$api->post('users','UserController@store');
		$api->patch('users/{id}','UserController@update');
		$api->delete('users/{id}','UserController@destroy');
	});
});
