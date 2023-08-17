<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/test', 'App\Http\Controllers\api\v1\TestController@index')->middleware(['auth:sanctum', 'abilities:test:test']);

Route::prefix('/auth')->group(function() {
    Route::post('/get-token', 'App\Http\Controllers\api\v1\AuthController@getToken');
});

Route::prefix('/message')->group(function() {
    Route::get('', 'App\Http\Controllers\api\v1\MessageController@index')->middleware(['auth:sanctum', 'abilities:message:get']);;
    Route::post('', 'App\Http\Controllers\api\v1\MessageController@store')->middleware(['auth:sanctum', 'abilities:message:store']);
});

