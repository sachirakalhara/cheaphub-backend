<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Category\CategoryController;
use App\Http\Controllers\API\CoinbasePaymentController;
use App\Http\Controllers\API\Product\Bulk\BulkProductController;
use App\Http\Controllers\API\Product\Contribution\ContributionProductController;
use App\Http\Controllers\API\Subscription\PackageController;
use App\Http\Controllers\API\Subscription\SubscriptionController;
use App\Http\Controllers\API\Tag\TagController;
use App\Http\Controllers\API\User\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MarxPaymentController;


Route::post('/v1/register', [AuthController::class, 'register']);
Route::post('/v1/login', [AuthController::class, 'login']);
Route::get('/v1/confirm-email/{user_id}/{key}', [AuthController::class, 'confirmMail']);

Route::post('/coinbase/payment', [CoinbasePaymentController::class, 'createPayment']);
Route::post('/coinbase/callback', [CoinbasePaymentController::class, 'paymentCallback']);
Route::post('payment',[MarxPaymentController::class, 'createPayment']);
Route::get('cancel',[MarxPaymentController::class, 'cancel']);
Route::get('payment/success', [MarxPaymentController::class, 'success']);

//public & Auth Related API
Route::post('/v1/bulk/product/get-all', [BulkProductController::class, 'index']);
Route::get('/v1/bulk/slug-product/{slug_name}', [BulkProductController::class, 'findBySlug'])->name('api.slug-product.slug');

Route::post('/v1/tag/get-all', [TagController::class, 'index']);
Route::get('/v1/tag/{id}', [TagController::class, 'findById']);

Route::post('/v1/category/get-all', [CategoryController::class, 'index']);
Route::post('/v1/package/get-all', [PackageController::class, 'index']);
Route::post('/v1/subscription/get-all', [SubscriptionController::class, 'index']);

Route::post('/v1/contribution/product/get-all', [ContributionProductController::class, 'index']);
Route::get('/v1/contribution/slug-contribution-product/{slug_name}', [ContributionProductController::class, 'findBySlug'])->name('api.slug-contribution-product.slug');
Route::get('/v1/contribution/product/{id}', [ContributionProductController::class, 'findById']);




// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['prefix' => 'admin'], function () {

            Route::group(['prefix' => 'super-admin','middleware' =>['super_admin']], function () {
                Route::post('/bulk/product/create', [BulkProductController::class, 'store']);
                Route::put('/bulk/product/update', [BulkProductController::class, 'update']);

                Route::post('/contribution/product/create', [ContributionProductController::class, 'store']);
                Route::put('/contribution/product/update', [ContributionProductController::class, 'update']);

                Route::post('/subscription/create', [SubscriptionController::class, 'store']);
                Route::delete('/subscription/delete/{product_id}', [SubscriptionController::class, 'delete']);

                Route::post('/package/create', [PackageController::class, 'store']);
                Route::delete('/package/delete/{package_id}', [PackageController::class, 'delete']);


                Route::post('/category/create', [CategoryController::class, 'store']);
                Route::put('/category/update', [CategoryController::class, 'update']);
                Route::delete('/category/{id}', [CategoryController::class, 'delete']);

                Route::post('/tag/create', [TagController::class, 'store']);
                Route::put('/tag/update', [TagController::class, 'update']);
                Route::delete('/tag/{id}', [TagController::class, 'delete']);

            });
        });
    Route::post('/user/get-all', [UserController::class, 'index']);
        Route::post('/user/update', [UserController::class, 'update']);

        Route::post('/logout', [AuthController::class, 'logout']);
    });

});

