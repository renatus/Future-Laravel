<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\User\AddUserController;
use App\Http\Controllers\V1\User\LoginUserController;
use App\Http\Controllers\V1\User\LogoutUserController;
use App\Http\Controllers\V1\Notebook\AddNotebookController;
use App\Http\Controllers\V1\Notebook\DelNotebookController;
use App\Http\Controllers\V1\Notebook\EditNotebookController;
use App\Http\Controllers\V1\Notebook\ShowNotebookController;
use App\Http\Controllers\V1\Notebook\ShowNotebooksController;

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

//Public V1 routes
Route::group([
    'prefix' => 'v1'
], function () {
    // Add user
    Route::post('/register', AddUserController::class);
    // Log user in
    Route::post('/login', LoginUserController::class);
    // Display particular entry
    Route::get('/notebook/{id}', ShowNotebookController::class);
    // Display all entries
    Route::get('/notebook', ShowNotebooksController::class);
});

//Protected V1 routes
Route::group([
    'prefix' => 'v1',
    'middleware' => ['auth:sanctum']
], function () {
    // Modify entry
    Route::post('/notebook/{id}', EditNotebookController::class);
    // Add entry
    Route::post('/notebook', AddNotebookController::class);
    // Delete entry
    Route::delete('/notebook/{id}', DelNotebookController::class);
    // Log user out
    Route::get('/logout', LogoutUserController::class);
});
