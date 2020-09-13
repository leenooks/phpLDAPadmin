<?php

use Illuminate\Support\Facades\Route;

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

/*
*/
Auth::routes([
	'reset' => false,
	'verify' => false,
	'register' => false,
]);
Route::redirect('/','home');
Route::get('logout','Auth\LoginController@logout');
Route::get('home','HomeController@home');
Route::post('render','HomeController@render');
