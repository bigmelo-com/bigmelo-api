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
    Route::post('/signup', 'App\Http\Controllers\api\v1\AuthController@signUp');
});

Route::prefix('/message')->group(function() {
    Route::get('', 'App\Http\Controllers\api\v1\MessageController@index')->middleware(['auth:sanctum', 'abilities:message:get']);
    Route::post('', 'App\Http\Controllers\api\v1\MessageController@store')->middleware(['auth:sanctum', 'abilities:message:store']);
});

Route::prefix('chat')->group(function() {
    Route::get('', 'App\Http\Controllers\api\v1\ChatController@index')->middleware(['auth:sanctum', 'abilities:chats:get']);
});

Route::prefix('/user')->group(function() {
    Route::post('', 'App\Http\Controllers\api\v1\UserController@store')->middleware(['auth:sanctum', 'abilities:user:store']);
    Route::post('/validate', 'App\Http\Controllers\api\v1\UserController@validateUser')->middleware(['auth:sanctum', 'abilities:code:validate']);
    Route::patch('/validation-code', 'App\Http\Controllers\api\v1\UserController@createValidationCode')->middleware(['auth:sanctum', 'abilities:code:get-validation-code']);
    Route::post('/{user_id}/messages-limit', 'App\Http\Controllers\api\v1\UserMessagesLimitController@store')->middleware(['auth:sanctum', 'abilities:user:store']);
});

Route::prefix('/profile')->group(function() {
    Route::get('', 'App\Http\Controllers\api\v1\ProfileController@getProfileInfo')->middleware(['auth:sanctum', 'abilities:profile:get']);
});

Route::prefix('/plan')->group(function() {
    Route::post('', 'App\Http\Controllers\api\v1\PlanController@store')->middleware(['auth:sanctum', 'abilities:plan:store']);
    Route::patch('/{plan_id}', 'App\Http\Controllers\api\v1\PlanController@update')->middleware(['auth:sanctum', 'abilities:plan:update']);
});

Route::prefix('/project')->group(function() {
    Route::post('', 'App\Http\Controllers\api\v1\ProjectController@store')->middleware(['auth:sanctum', 'abilities:project:store']);

    Route::prefix('/{project_id}')->group(function() {
        Route::patch('', 'App\Http\Controllers\api\v1\ProjectController@update')->middleware(['auth:sanctum', 'abilities:project:store']);
        Route::post('/embeddings', 'App\Http\Controllers\api\v1\ProjectEmbeddingController@store_embeddings')->middleware(['auth:sanctum', 'abilities:project:store-embeddings']);
        Route::post('/content', 'App\Http\Controllers\api\v1\ProjectEmbeddingController@store')->middleware(['auth:sanctum', 'abilities:project:store']);
        Route::get('/plan', 'App\Http\Controllers\api\v1\PlanController@index')->middleware(['auth:sanctum', 'abilities:plan:get']);
    });
});

Route::prefix('/organization')->group(function() {
    Route::get('', 'App\Http\Controllers\api\v1\OrganizationController@index')->middleware(['auth:sanctum', 'abilities:organization:list']);
    Route::post('', 'App\Http\Controllers\api\v1\OrganizationController@store')->middleware(['auth:sanctum', 'abilities:organization:store']);
    Route::patch('/{organization_id}', 'App\Http\Controllers\api\v1\OrganizationController@update')->middleware(['auth:sanctum', 'abilities:organization:store']);
    Route::get('/{organization_id}/projects', 'App\Http\Controllers\api\v1\ProjectController@index')->middleware(['auth:sanctum', 'abilities:project:list']);
});
