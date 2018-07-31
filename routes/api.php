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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->post('/v1/lots', 'LotController@add')->name('addLot');
Route::get('/v1/lots/{id}', 'LotController@show');
Route::get('/v1/lots', 'LotController@list');
Route::middleware('auth:api')->post('/v1/trades', 'LotController@buy')->name('buyLot');
