<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotebookController;

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

//Public routes
Route::group([
    'prefix' => 'v1'
], function () {
    // Add user
    Route::post('/register', [AuthController::class, 'register']);
    // Log user in
    Route::post('/login', [AuthController::class, 'login']);
    // Get particular entry
    Route::get('/notebook/{id}', [NotebookController::class, 'show']);
    // Get all entries
    Route::get('/notebook', [NotebookController::class, 'index']);
});

//Protected routes
Route::group([
    'prefix' => 'v1',
    'middleware' => ['auth:sanctum']
], function () {
    // Modify entry
    Route::post('/notebook/{id}', [NotebookController::class, 'update']);
    // Add entry
    Route::post('/notebook', [NotebookController::class, 'add']);
    // Delete entry
    Route::delete('/notebook/{id}', [NotebookController::class, 'destroy']);
    // Log user out
    Route::get('/logout', [AuthController::class, 'logout']);
});
