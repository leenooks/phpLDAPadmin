<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{HomeController,ImportController};
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
Route::get('debug',[HomeController::class,'debug']);
Route::get('import',[HomeController::class,'import_frame']);
Route::get('schema',[HomeController::class,'schema_frame']);

Route::get('logout',[LoginController::class,'logout']);

Route::group(['prefix'=>'user'],function() {
	Route::get('image',[HomeController::class,'user_image']);
});

Route::post('entry/update/commit',[HomeController::class,'entry_update']);
Route::post('entry/update/pending',[HomeController::class,'entry_pending_update']);
Route::get('entry/newattr/{id}',[HomeController::class,'entry_newattr']);
Route::get('entry/export/{id}',[HomeController::class,'entry_export']);
Route::post('entry/password/check/',[HomeController::class,'entry_password_check']);

Route::post('import/process/{type}',[HomeController::class,'import']);