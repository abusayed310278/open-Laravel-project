<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RefundRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Mobile App API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmailOtp']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-reset-otp', [AuthController::class, 'verifyResetOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/categories', [ProductController::class, 'categories']);
Route::get('/brands', [ProductController::class, 'brands']);
Route::get('/sellers/{id}', [ProductController::class, 'seller']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/profile', [AuthController::class, 'updateProfile']);
    });

    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::put('/update/{product}', [CartController::class, 'update']);
        Route::delete('/remove/{product}', [CartController::class, 'remove']);
        Route::delete('/', [CartController::class, 'clear']);
        Route::post('/clear', [CartController::class, 'clear']);
    });

    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::put('/{id}', [AddressController::class, 'update']);
        Route::post('/{id}/default', [AddressController::class, 'setDefault']);
        Route::delete('/{id}', [AddressController::class, 'destroy']);
    });

    Route::match(['get', 'post'], '/checkout/payment-methods', [CheckoutController::class, 'availablePaymentMethods']);
    Route::post('/checkout', [CheckoutController::class, 'process']);
    Route::post('/checkout/stripe-confirm/{order}', [CheckoutController::class, 'confirmStripe']);

    Route::prefix('orders')->group(function () {
        Route::get('/', [CustomerOrderController::class, 'index']);
        Route::get('/{order}', [CustomerOrderController::class, 'show']);
    });

    Route::post('/vendor-orders/{vendorOrder}/refund-request', [RefundRequestController::class, 'store']);
    Route::post('/vendor-orders/{vendorOrder}/payment-proof', [CustomerOrderController::class, 'submitPaymentProof']);
    Route::get('/refunds', [RefundRequestController::class, 'index']);
});