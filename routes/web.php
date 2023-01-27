<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => LaravelLocalization::setLocale()], function() {
	Auth::routes([
		'login' => TRUE,
		'logout' => TRUE,
		'reset' => FALSE,
		'confirm' => FALSE,
		'verify' => FALSE,
		'register' => FALSE,
	]);

	Route::get('/',[HomeController::class,'home']);
	Route::get('info',[HomeController::class,'info']);
	Route::post('dn',[HomeController::class,'dn_frame']);
});

Route::get('logout',[LoginController::class,'logout']);

Route::group(['prefix'=>'user'],function() {
	Route::get('image',[HomeController::class,'user_image']);
});
