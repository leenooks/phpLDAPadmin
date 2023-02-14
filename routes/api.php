<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\APIController;

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

Route::group([],function() {
	Route::get('bases',[APIController::class,'bases']);
	Route::get('children',[APIController::class,'children']);
	Route::post('schema/view',[APIController::class,'schema_view']);
});

Route::group(['middleware'=>'auth:api','prefix'=>'user'],function() {
});
