<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{AjaxController,EntryController,HomeController,SearchController};
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
Route::post('search',[SearchController::class,'search']);

Route::controller(EntryController::class)
	->prefix('entry')
	->group(function() {
		Route::middleware(AllowAnonymous::class)->group(function() {
			Route::match(['get','post'],'add','add');
			Route::post('attr/add/{id}','attr_add');
			Route::post('copy-move','copy_move');
			Route::post('create','create');
			Route::post('delete','delete');
			Route::get('export/{id}','export');
			Route::view('import','frames.import');
			Route::post('import/process/{type}','import_process');
			Route::post('objectclass/add','objectclass_add');
			Route::post('password/check','password_check');
			Route::post('rename','rename');
			Route::post('update/commit','update_commit');
			Route::post('update/pending','update_pending');
		});
	});

Route::controller(HomeController::class)->group(function() {
	Route::middleware(AllowAnonymous::class)->group(function() {
		Route::get('/','home');
		Route::view('debug','debug');
		Route::post('frame','frame');

		Route::group(['prefix'=>'modal'],function() {
			Route::view('copy-move/{dn}','modals.entry-copy-move');
			Route::view('delete/{dn}','modals.entry-delete');
			Route::view('export/{dn}','modals.entry-export');
			Route::view('member-manage/{dn}','modals.member-manage');
			Route::view('rename/{dn}','modals.entry-rename');
			Route::view('userpassword-check/{dn}','modals.entry-userpassword-check');
		});

		Route::group(['prefix'=>'server'],function() {
			Route::view('info','frames.info');
			Route::get('schema','frame_schema');
		});

		Route::group(['prefix'=>'user'],function() {
			Route::get('image','user_image');
		});
	});
});

Route::controller(AjaxController::class)
	->prefix('ajax')
	->group(function() {
		Route::post('bases','bases');
		Route::post('children','children');
		Route::post('member/member','member_member');
		Route::post('schema/view','schema_view');
		Route::post('schema/objectclass/attrs/{id}','schema_objectclass_attrs');
		Route::post('subordinates','subordinates');
	});