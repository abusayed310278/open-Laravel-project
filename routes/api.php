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
    Route::post('/resend-email-otp', [AuthController::class, 'resendEmailOtp']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-reset-otp', [AuthController::class, 'verifyResetOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/social-login', [AuthController::class, 'socialLogin']);
    Route::post('/google', [AuthController::class, 'socialLogin']);
    Route::post('/facebook', [AuthController::class, 'socialLogin']);
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
        Route::put('/password', [AuthController::class, 'changePassword']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/email/verification-notification', [AuthController::class, 'resendEmailOtp']);
        Route::post('/verify-phone/send', [AuthController::class, 'sendPhoneOtp']);
        Route::post('/verify-phone/confirm', [AuthController::class, 'confirmPhoneOtp']);
    });

    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::put('/update/{product}', [CartController::class, 'update']);
        Route::delete('/remove/{product}', [CartController::class, 'remove']);
        Route::delete('/', [CartController::class, 'clear']);
        Route::post('/clear', [CartController::class, 'clear']);
    });

    Route::post('/onboarding/address', [AddressController::class, 'store']);

        // Cart
        Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'index']);
        Route::post('/cart/add', [\App\Http\Controllers\Api\CartController::class, 'add']);
        Route::put('/cart/update/{product}', [\App\Http\Controllers\Api\CartController::class, 'update']);
        Route::delete('/cart/remove/{product}', [\App\Http\Controllers\Api\CartController::class, 'remove']);
        Route::post('/cart/clear', [\App\Http\Controllers\Api\CartController::class, 'clear']);

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

/*
|--------------------------------------------------------------------------
| Seller Mobile App API Routes
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\Seller\SellerAuthController;
use App\Http\Controllers\Api\Seller\SellerDashboardController;
use App\Http\Controllers\Api\Seller\SellerContentController;
use App\Http\Controllers\Api\Seller\SellerOrderController;
use App\Http\Controllers\Api\Seller\SellerProductController;
use App\Http\Controllers\Api\Seller\SellerProfileController;
use App\Http\Controllers\Api\Seller\SellerWalletController;

Route::prefix('seller')->group(function () {
    Route::post('/auth/login', [SellerAuthController::class, 'login']);
    Route::post('/auth/register', [SellerAuthController::class, 'register']);
    Route::post('/auth/forgot-password', [SellerAuthController::class, 'forgotPassword']);
    Route::post('/auth/verify-reset-otp', [SellerAuthController::class, 'verifyResetOtp']);
    Route::post('/auth/reset-password', [SellerAuthController::class, 'resetPassword']);
    Route::post('/auth/social-login', [SellerAuthController::class, 'socialLogin']);
    Route::post('/auth/google', [SellerAuthController::class, 'socialLogin']);
    Route::post('/auth/facebook', [SellerAuthController::class, 'socialLogin']);
    Route::post('/auth/verify-email', [SellerAuthController::class, 'verifyEmailOtp']);
    Route::post('/auth/resend-email-otp', [SellerAuthController::class, 'resendEmailOtp']);

    // Help, Guidelines & Legal
    Route::get('/help-guidelines', [SellerContentController::class, 'helpGuidelines']);
    Route::get('/terms-of-service', [SellerContentController::class, 'termsOfService']);
    Route::get('/categories', [\App\Http\Controllers\Api\ProductController::class, 'categories']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [SellerAuthController::class, 'me']);
        Route::post('/auth/logout', [SellerAuthController::class, 'logout']);
        Route::put('/auth/password', [SellerAuthController::class, 'changePassword']);
        Route::post('/auth/change-password', [SellerAuthController::class, 'changePassword']);
        Route::post('/auth/email/verification-notification', [SellerAuthController::class, 'resendEmailOtp']);
        Route::post('/auth/verify-phone/send', [SellerAuthController::class, 'sendPhoneOtp']);
        Route::post('/auth/verify-phone/confirm', [SellerAuthController::class, 'confirmPhoneOtp']);
        Route::post('/onboarding/address', [AddressController::class, 'store']);

        // Cart
        Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'index']);
        Route::post('/cart/add', [\App\Http\Controllers\Api\CartController::class, 'add']);
        Route::put('/cart/update/{product}', [\App\Http\Controllers\Api\CartController::class, 'update']);
        Route::delete('/cart/remove/{product}', [\App\Http\Controllers\Api\CartController::class, 'remove']);
        Route::post('/cart/clear', [\App\Http\Controllers\Api\CartController::class, 'clear']);

        Route::prefix('addresses')->group(function () {
            Route::get('/', [AddressController::class, 'index']);
            Route::post('/', [AddressController::class, 'store']);
            Route::put('/{id}', [AddressController::class, 'update']);
            Route::post('/{id}/default', [AddressController::class, 'setDefault']);
            Route::delete('/{id}', [AddressController::class, 'destroy']);
        });

        // Dashboard Analytics
        Route::get('/dashboard', [SellerDashboardController::class, 'index']);

        // Orders Management
        Route::get('/orders', [SellerOrderController::class, 'index']);
        Route::get('/orders/{vendorOrder}', [SellerOrderController::class, 'show']);
        Route::post('/orders/{vendorOrder}/status', [SellerOrderController::class, 'updateStatus']);

        // Product Catalog & Metadata
        Route::get('/products/meta', [SellerProductController::class, 'meta']);
        Route::get('/products', [SellerProductController::class, 'index']);
        Route::post('/products', [SellerProductController::class, 'store']);
        Route::get('/products/{product}', [SellerProductController::class, 'show']);
        Route::post('/products/{product}', [SellerProductController::class, 'update']);
        Route::patch('/products/{product}/toggle-publish', [SellerProductController::class, 'togglePublish']);
        Route::delete('/products/{product}', [SellerProductController::class, 'destroy']);

        // Wallet & Payouts
        Route::get('/wallet', [SellerWalletController::class, 'index']);
        Route::post('/wallet/payout-request', [SellerWalletController::class, 'requestPayout']);

        // Store Profile & General Settings
        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'notifications']);
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'markNotificationAsRead']);

        Route::get('/profile', [SellerProfileController::class, 'show']);
        Route::post('/profile', [SellerProfileController::class, 'update']);

        // Verification & KYC Status
        Route::get('/verification', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'verification']);
        Route::post('/verification', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'submitVerification']);

        // Subscription Plans
        Route::get('/subscriptions', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'subscriptions']);

        // Payment Settings
        Route::get('/payment-settings', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'paymentSettings']);
        Route::post('/payment-settings', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'updatePaymentSettings']);

        // Support Tickets & Messages
        Route::get('/support-tickets', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'supportTickets']);
        Route::post('/support-tickets', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'storeSupportTicket']);

        // Inspection Queue
        Route::get('/inspections', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'inspections']);

        // Warehouse Deposits
        Route::get('/warehouse-deposits', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'warehouseDeposits']);
        Route::post('/warehouse-deposits', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'storeWarehouseDeposit']);

        // Manage Inventory
        Route::get('/inventory', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'inventory']);
        Route::patch('/inventory/{product}', [\App\Http\Controllers\Api\Seller\SellerOperationsController::class, 'updateInventory']);

        // Direct Messages & Chat
        Route::get('/messages', [\App\Http\Controllers\Api\Seller\SellerChatController::class, 'index']);
        Route::post('/messages/start', [\App\Http\Controllers\Api\Seller\SellerChatController::class, 'start']);
        Route::get('/messages/{conversation}', [\App\Http\Controllers\Api\Seller\SellerChatController::class, 'show']);
        Route::post('/messages/{conversation}', [\App\Http\Controllers\Api\Seller\SellerChatController::class, 'store']);
        Route::get('/messages/attachment/{message}', [\App\Http\Controllers\Api\Seller\SellerChatController::class, 'attachment'])->name('api.seller.chat.attachment');
    });
});