<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Category\CategoryController;
use App\Http\Controllers\API\CoinbasePaymentController;
use App\Http\Controllers\API\Product\Bulk\BulkProductController;
use App\Http\Controllers\API\Product\Contribution\ContributionProductController;
use App\Http\Controllers\API\Subscription\MonthController;
use App\Http\Controllers\API\Subscription\RegionController;
use App\Http\Controllers\API\Subscription\SubscriptionController;
use App\Http\Controllers\API\Tag\TagController;
use App\Http\Controllers\API\User\UserController;
use App\Http\Controllers\CoingateController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\API\Subscription\PackageController;
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


Route::post('/coinbase/payment', [CoinbasePaymentController::class, 'createPayment']);
Route::post('/coinbase/callback', [CoinbasePaymentController::class, 'paymentCallback']);

// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::group(['prefix' => 'v1'], function () {


        Route::group(['prefix' => 'super-admin','middleware' =>['super_admin']], function () {
            Route::post('/bulk/product/create', [BulkProductController::class, 'store']);
            Route::put('/bulk/product/update', [BulkProductController::class, 'update']);
            Route::post('/bulk/product/get-all', [BulkProductController::class, 'index']);
            Route::get('/bulk/product/{id}', [BulkProductController::class, 'findById']);
            //slug Url
            Route::get('/bulk/slug-product/{slug_name}', [BulkProductController::class, 'findBySlug'])->name('api.slug-product.slug');


            Route::post('/contribution/product/create', [ContributionProductController::class, 'store']);
            Route::put('/contribution/product/update', [ContributionProductController::class, 'update']);
            Route::post('/contribution/product/get-all', [ContributionProductController::class, 'index']);
            Route::get('/contribution/slug-contribution-product/{slug_name}', [ContributionProductController::class, 'findBySlug'])->name('api.slug-contribution-product.slug');
            Route::get('/contribution/product/{id}', [ContributionProductController::class, 'findById']);

            Route::post('/subscription/create', [SubscriptionController::class, 'store']);
            Route::post('/subscription/get-all', [SubscriptionController::class, 'index']);
            Route::delete('/subscription/delete/{product_id}', [SubscriptionController::class, 'delete']);

            Route::post('/package/create', [PackageController::class, 'store']);
            Route::post('/package/get-all', [PackageController::class, 'index']);
            Route::delete('/package/delete/{package_id}', [PackageController::class, 'delete']);


            Route::post('/category/get-all', [CategoryController::class, 'index']);
            Route::post('/category/create', [CategoryController::class, 'store']);
            Route::put('/category/update', [CategoryController::class, 'update']);
            Route::delete('/category/{id}', [CategoryController::class, 'delete']);

            Route::post('/tag/get-all', [TagController::class, 'index']);
            Route::post('/tag/create', [TagController::class, 'store']);
            Route::put('/tag/update', [TagController::class, 'update']);
            Route::delete('/tag/{id}', [TagController::class, 'delete']);
            Route::get('/tag/{id}', [TagController::class, 'findById']);

            Route::post('/month/get-all', [MonthController::class, 'index']);

            Route::post('/region/get-all', [RegionController::class, 'index']);
            Route::post('/region/create', [RegionController::class, 'store']);
            Route::put('/region/update', [RegionController::class, 'update']);
            Route::delete('/region/delete/{id}', [RegionController::class, 'delete']);





        });
        Route::post('/user/get-all', [UserController::class, 'index']);
        Route::post('/user/update', [UserController::class, 'update']);

        Route::post('/logout', [AuthController::class, 'logout']);
    });

});

