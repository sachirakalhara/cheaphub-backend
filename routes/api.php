<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Cart\CartController;
use App\Http\Controllers\API\Cart\CartItemController;
use App\Http\Controllers\API\Category\CategoryController;
use App\Http\Controllers\API\CoinbasePaymentController;
use App\Http\Controllers\API\Coupon\CouponController;
use App\Http\Controllers\API\Product\Bulk\BulkProductController;
use App\Http\Controllers\API\Product\Contribution\ContributionProductController;
use App\Http\Controllers\API\Subscription\PackageController;
use App\Http\Controllers\API\Subscription\SubscriptionController;
use App\Http\Controllers\API\Tag\TagController;
use App\Http\Controllers\API\User\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MarxPaymentController;
use App\Http\Controllers\API\Notification\NotificationController;
use App\Http\Controllers\API\Payment\OrderNoteController;
use App\Http\Controllers\API\Payment\OrderController;
use App\Http\Controllers\API\Payment\WalletController;
use App\Http\Controllers\API\Product\Contribution\ProductReplacementController;
use App\Http\Controllers\API\Review\ReviewController;
use App\Http\Controllers\API\Ticket\TicketController;
use App\Models\Product\Contribution\ProductReplacement;

Route::post('/v1/register', [AuthController::class, 'register']);
Route::post('/v1/login', [AuthController::class, 'login']);
Route::get('/v1/confirm-email/{user_id}/{key}', [AuthController::class, 'confirmMail']);

Route::post('/v1/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/v1/reset-password', [AuthController::class, 'password_reset'])->name('reset.password');

Route::post('/coinbase/payment', [CoinbasePaymentController::class, 'createPayment']);
Route::post('/coinbase/callback', [CoinbasePaymentController::class, 'paymentCallback'])->name('coinbase.callback');

// Route::post('payment',[MarxPaymentController::class, 'createPayment']);
// Route::get('cancel',[MarxPaymentController::class, 'cancel']);
// Route::get('payment/success', [MarxPaymentController::class, 'success']);

//public & Auth Related API
Route::post('/v1/bulk/product/get-all', [BulkProductController::class, 'index']);
// Route::get('/v1/bulk/slug-product/{slug_name}', [BulkProductController::class, 'findBySlug'])->name('api.slug-product.slug');
Route::post('/v1/bulk/product/filter', [BulkProductController::class, 'filter']);
Route::get('/v1/bulk/product/{id}', [BulkProductController::class, 'findById']);


Route::post('/v1/tag/get-all', [TagController::class, 'index']);
Route::get('/v1/tag/{id}', [TagController::class, 'findById']);
Route::post('/v1/tag/filter', [TagController::class, 'filter']);

Route::post('/v1/category/get-all', [CategoryController::class, 'index']);
Route::post('/v1/category/filter', [CategoryController::class, 'filter']);
Route::get('/v1/category/trending', [CategoryController::class, 'trendingCategory']);

Route::post('/v1/package/get-all', [PackageController::class, 'index']);
Route::post('/v1/subscription/get-all', [SubscriptionController::class, 'index']);

Route::post('/v1/contribution/product/get-all', [ContributionProductController::class, 'index']);
// Route::get('/v1/contribution/slug-contribution-product/{slug_name}', [ContributionProductController::class, 'findBySlug'])->name('api.slug-contribution-product.slug');
Route::get('/v1/contribution/product/{id}', [ContributionProductController::class, 'findById']);
Route::post('/v1/contribution/product/filter', [ContributionProductController::class, 'filter']);




// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAllAsRead']);
        
        Route::get('/order/{id}', [OrderController::class, 'getOrderByID']);
        Route::get('/orders/{user_id}', [OrderController::class, 'getOrdersByUserID']);
        Route::get('/wallet-orders/{user_id}', [OrderController::class, 'getWalletOrdersByUserID']);
        Route::post('/order/filter', [OrderController::class, 'filter']);
        
        Route::post('/ticket/get-all', [TicketController::class, 'index']);
        Route::post('/ticket', [TicketController::class, 'store']);
        Route::post('/ticket/comment', [TicketController::class, 'addComment']);
        Route::post('/ticket/status-change', [TicketController::class, 'statusChange']);

        Route::post('/review/create', [ReviewController::class, 'store']);
        Route::put('/review/update', [ReviewController::class, 'update']);
        Route::delete('/review/{id}', [ReviewController::class, 'deleteReview']);


        Route::group(['prefix' => 'super-admin','middleware' =>['super_admin']], function () {
            
            Route::get('/total-customer-spend', [OrderController::class, 'totalCustomerWithSpend']);
            Route::post('/order/status-change', [OrderController::class, 'changeStatus']);

            Route::post('/order/add-note', [OrderNoteController::class, 'addNote']);
            Route::get('/order/notes/{order_id}', [OrderNoteController::class, 'getNotesByOrderID']);

            Route::post('/coupon/create', [CouponController::class, 'store']);
            Route::put('/coupon/update', [CouponController::class, 'update']);
            Route::post('/coupon/get-all', [CouponController::class, 'index']);
            Route::delete('/coupon/{id}', [CouponController::class, 'delete']);

            Route::post('/bulk/product/create', [BulkProductController::class, 'store']);
            Route::put('/bulk/product/update', [BulkProductController::class, 'update']);
            Route::delete('/bulk/product/delete/{bulk_product_id}', [BulkProductController::class, 'delete']);
            Route::post('/bulk/product/get-all-visibility', [BulkProductController::class, 'getAllWithVisibility']);

            Route::post('/contribution/product/create', [ContributionProductController::class, 'store']);
            Route::put('/contribution/product/update', [ContributionProductController::class, 'update']);
            Route::delete('/contribution/product/delete/{id}', [ContributionProductController::class, 'delete']);
            Route::post('/contribution/product/get-all-visibility', [ContributionProductController::class, 'getAllWithVisibility']);

            Route::post('/subscription/create', [SubscriptionController::class, 'store']);
            Route::put('/subscription/update', [SubscriptionController::class, 'update']);
            Route::delete('/subscription/{subscription_id}', [SubscriptionController::class, 'deleteBydID']);

            Route::post('/package/create', [PackageController::class, 'store']);
            Route::delete('/package/delete/{package_id}', [PackageController::class, 'delete']);
            Route::put('/package/update', [PackageController::class, 'update']);

            
            Route::post('/category/create', [CategoryController::class, 'store']);
            Route::post('/category/update', [CategoryController::class, 'update']);
            Route::delete('/category/{id}', [CategoryController::class, 'delete']);

            Route::post('/tag/create', [TagController::class, 'store']);
            Route::put('/tag/update', [TagController::class, 'update']);
            Route::delete('/tag/{id}', [TagController::class, 'delete']);

        });

        Route::group(['prefix' => 'customer'], function () {
            Route::post('/marxpay/payment', [MarxPaymentController::class, 'makePayment']);
            Route::post('/marxpay/callback', [MarxPaymentController::class, 'paymentCallback'])->name('marxpay.callback');

            Route::post('/wallet/product-payment', [WalletController::class, 'processWalletPaymentForProduct']);

            Route::get('/wallet/show', [WalletController::class, 'show']);
             
            Route::post('/cart-item/add', [CartItemController::class, 'addToCart']);
            Route::delete('/cart-item/delete/{id}', [CartItemController::class, 'removeItemByItemId']);

            Route::post('/cart/detail', [CartController::class, 'cartDetails']);
            Route::get('/cart', [CartController::class, 'getCart']);
            Route::delete('/cart/{id}', [CartController::class, 'removeFromCart']);
            Route::delete('/cart/clear/all', [CartController::class, 'clearCart']);
            
            Route::get('package/replace/{package_id}', [ProductReplacementController::class, 'getAvalableCount']);
            Route::post('package/replace/store', [ProductReplacementController::class, 'store']);

            Route::get('/order/wallet/history', [OrderController::class, 'walletHistory']);


        });


        Route::post('/user/get-all', [UserController::class, 'index']);
        Route::post('/user/update', [UserController::class, 'update']);
        Route::get('/user/{id}', [UserController::class, 'getUserByID']);
        Route::get('/user/info/{id}', [UserController::class, 'getUserInfoByID']);
        

        Route::post('/logout', [AuthController::class, 'logout']);
    });

});

