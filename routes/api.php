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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);

Route::group(['middleware' => 'auth:api'], function() {

    Route::get('/validatedToken', function() {
       return true;
    });

    Route::get('data_items/status', [\App\Http\Controllers\DataVoalleController::class, 'getFilters']);
    Route::get('data_items/vendors', [\App\Http\Controllers\DataVoalleController::class, 'getVendors']);
    Route::get('data_items/supervisors', [\App\Http\Controllers\DataVoalleController::class, 'getSupervisors']);
    Route::get('data_items/supervisor_data', [\App\Http\Controllers\DataVoalleController::class, 'getSupervisorData']);
    Route::get('data_items/supervisor_amount', [\App\Http\Controllers\DataVoalleController::class, 'getSupervisorAmount']);
    Route::get('data_items/supervisor_team', [\App\Http\Controllers\DataVoalleController::class, 'getSupervisorTeam']);
    Route::resource('users', \App\Http\Controllers\UsersController::class);
    Route::get('data_items/filter-sales', [\App\Http\Controllers\DataVoalleController::class, 'filterSalesVendor']);
    Route::resource('data_voalle', \App\Http\Controllers\DataVoalleController::class);
    Route::resource('collaborator', \App\Http\Controllers\CollaboratorController::class);


});

Route::get('teste', [\App\Http\Controllers\TestController::class, 'index']);
