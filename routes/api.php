<?php


use App\Http\Controllers\ServerController;
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

Route::prefix('v1')->group(function () {
    // Automatically creates the standard CRUD routes: index, store, show, update, destroy
    Route::apiResource('servers', 'ServerController');

    // Custom route for server request history
    Route::get('servers/{server}/history', 'ServerController@requestsHistory');

    // Custom route for checking server health at a specific timestamp
    Route::get('servers/{server}/status/{timestamp}', 'ServerController@healthAtTimestamp');
});

