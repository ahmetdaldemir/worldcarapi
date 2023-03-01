<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
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

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('destination', [ApiController::class, 'destination']);
Route::get('location', [ApiController::class, 'location']);
Route::get('blog', [ApiController::class, 'blog']);
Route::get('comment', [ApiController::class, 'comment']);
Route::get('staticData', [ApiController::class, 'staticData']);
Route::get('language', [ApiController::class, 'language']);
Route::get('search', [ApiController::class, 'search']);
Route::get('/getEkstra', [ApiController::class, 'getEkstra']);
Route::post('/loginPerson', [AuthController::class, 'loginPerson']);



// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/reservation_history', [ApiController::class, 'reservation_history']);
    Route::get('/getUser', [ApiController::class, 'getUser']);
    Route::get('/userUpdate', [ApiController::class, 'userUpdate']);
    Route::post('/getOperation', [OperationController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);
});



Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
