<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\User\UserController;
use App\Http\Controllers\API\Product\ProductController;
use App\Http\Controllers\API\Category\CategoryController;
use App\Http\Controllers\API\Serial\SerialController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/v1/register', [AuthController::class, 'register']);
Route::post('/v1/login', [AuthController::class, 'login']);
Route::get('/v1/confirm-email/{user_id}/{key}', [AuthController::class, 'confirmMail']);


// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['prefix' => 'super-admin','middleware' =>['super_admin']], function () {
            Route::post('/product/create', [ProductController::class, 'store']);
            Route::put('/product/update', [ProductController::class, 'update']);
            Route::post('/product/get-all', [ProductController::class, 'index']);
            Route::post('/product/update-product-serial', [ProductController::class, 'updateProductSerial']);

            Route::post('/category/get-all', [CategoryController::class, 'index']);
            Route::post('/category/create', [CategoryController::class, 'store']);
            Route::put('/category/update', [CategoryController::class, 'update']);
            Route::delete('/category/{id}', [CategoryController::class, 'delete']);

        });
        Route::post('/user/get-all', [UserController::class, 'index']);
        Route::post('/user/update', [UserController::class, 'update']);

        Route::post('/logout', [AuthController::class, 'logout']);
    });

});

