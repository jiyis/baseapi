<?php

use Illuminate\Http\Request;

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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

$api = app('Dingo\Api\Routing\Router');

app('Dingo\Api\Transformer\Factory')->setAdapter(function ($app) {
    $fractal = new League\Fractal\Manager;
    $fractal->setSerializer(new \App\Serializer\InnoSerializer());
    return new Dingo\Api\Transformer\Adapter\Fractal($fractal);
});

$api->version('v1',['namespace' => '\App\Http\Controllers\Api\V1', 'middleware' => []], function ($api) {
    $api->group([
//        'middleware' => '',
    ], function($api) {
        $api->get('users','UserController@index');
        $api->get('users/count','UserController@count');
        $api->get('users/{id}','UserController@show');
        $api->post('users','UserController@store');
        $api->patch('users/{id}','UserController@update');
        $api->delete('users/{id}','UserController@destroy');

    });
});
