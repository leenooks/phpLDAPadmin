<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\AllowAnonymous;

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

Route::get('logout',[LoginController::class,'logout']);

Route::controller(HomeController::class)->group(function() {
	Route::middleware(AllowAnonymous::class)->group(function() {
		Route::get('/','home');
		Route::view('info','frames.info');
		Route::view('debug','debug');
		Route::post('frame','frame');
		Route::view('import','frames.import');
		Route::get('schema','schema_frame');

		Route::group(['prefix'=>'user'],function() {
			Route::get('image','user_image');
		});

		Route::match(['get','post'],'entry/add','entry_add');
		Route::post('entry/create','entry_create');
		Route::post('entry/delete','entry_delete');
		Route::get('entry/export/{id}','entry_export');
		Route::post('entry/password/check/','entry_password_check');
		Route::post('entry/attr/add/{id}','entry_attr_add');
		Route::post('entry/objectclass/add','entry_objectclass_add');
		Route::post('entry/update/commit','entry_update');
		Route::post('entry/update/pending','entry_pending_update');

		Route::post('import/process/{type}','import');

		Route::view('modal/delete/{dn}','modals.entry-delete');
	});
});