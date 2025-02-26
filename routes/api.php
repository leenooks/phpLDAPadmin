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

Route::controller(APIController::class)->group(function() {
	Route::get('bases','bases');
	Route::get('children','children');
	Route::post('schema/view','schema_view');
	Route::post('schema/objectclass/attrs/{id}','schema_objectclass_attrs');
});