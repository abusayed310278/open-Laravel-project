<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AttributeGroupController;
use App\Http\Controllers\Admin\AttributeValueController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryAttributeController;
use App\Http\Controllers\Admin\CategoryBuilderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\CommissionRuleController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PaymentVerificationController as AdminPaymentVerificationController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ReviewReportController as AdminReviewReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SocialTypeController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\SupportController as AdminSupportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VerificationChecklistController;
use App\Http\Controllers\Admin\VerificationController as AdminVerificationController;
use App\Http\Controllers\Admin\VerificationLocationController;
use App\Http\Controllers\Admin\VerificationRequirementController;
use App\Http\Controllers\Admin\VerifierController;
use App\Http\Controllers\Admin\VisitorReportController;
use App\Http\Controllers\Admin\WarehouseController as AdminWarehouseController;
use App\Http\Controllers\Admin\WarehouseLocationController;
use App\Http\Controllers\Admin\WarehouseProductController;
use App\Http\Controllers\BlogPageController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryPageController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CmsPageController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\CustomerAddressController;
use App\Http\Controllers\CustomerInvoiceController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\CustomerReturnController;
use App\Http\Controllers\CustomerReviewController;
use App\Http\Controllers\CustomerSecurityController;
use App\Http\Controllers\CustomerWishlistController;
use App\Http\Controllers\GradingSystemController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ManualPaymentSubmissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPageController;
use App\Http\Controllers\ProductVerificationController;
use App\Http\Controllers\RefundRequestController;
use App\Http\Controllers\ReviewReportController;
use App\Http\Controllers\SellerInventoryController;
use App\Http\Controllers\SellerInvoiceController;
use App\Http\Controllers\SellerOrderController;
use App\Http\Controllers\SellerPaymentVerificationController;
use App\Http\Controllers\SellerRefundController;
use App\Http\Controllers\SellerReportController;
use App\Http\Controllers\SellerReviewController;
use App\Http\Controllers\SellerWalletController;
use App\Http\Controllers\StorePageController;
use App\Http\Controllers\StoreSettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\VendorPaymentSettingController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\Verifier\AppointmentController as VerifierAppointmentController;
use App\Http\Controllers\WarehouseDepositController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/grading-system', GradingSystemController::class)->name('grading-system');
Route::get('/shop', [ProductPageController::class, 'index'])->name('shop');
Route::get('/products/{product:slug}', [ProductPageController::class, 'show'])->name('products.show');
Route::get('/categories', [CategoryPageController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryPageController::class, 'show'])->name('categories.show');
Route::get('/stores', [StorePageController::class, 'index'])->name('stores.index');
Route::get('/store/{slug}', [StorePageController::class, 'business'])->name('stores.business');
Route::get('/seller/{slug}', [StorePageController::class, 'saler'])->name('stores.saler');

Route::get('/blog', [BlogPageController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogPageController::class, 'show'])->name('blog.show');
Route::get('/p/{page:slug}', [CmsPageController::class, 'show'])->name('pages.show');

/*
|--------------------------------------------------------------------------
| Cart — works for guests (session) and logged-in users (DB), same routes
|--------------------------------------------------------------------------
*/
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.add');
Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.remove');

Route::middleware('auth')->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/{product}/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/{product}/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.move-to-cart');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
    Route::get('/orders/{order}/confirmation', [CheckoutController::class, 'confirmation'])->name('orders.confirmation');

    Route::post('/reviews/{review}/report', [ReviewReportController::class, 'store'])->middleware('throttle:20,1')->name('reviews.report');

    Route::post('/products/{product}/chat', [ChatController::class, 'startFromProduct'])->name('chat.start');
    Route::post('/chat/{conversation}', [ChatController::class, 'store'])->middleware('throttle:30,1')->name('chat.store');
    Route::get('/chat/{conversation}/poll/{afterId}', [ChatController::class, 'poll'])->whereNumber('afterId')->name('chat.poll');
    Route::get('/chat/messages/{message}/attachment', [ChatController::class, 'attachment'])->name('chat.attachment');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('/notification-preferences', [NotificationPreferenceController::class, 'edit'])->name('notification-preferences.edit');
    Route::post('/notification-preferences', [NotificationPreferenceController::class, 'update'])->name('notification-preferences.update');
});

/*
|--------------------------------------------------------------------------
| Seller product routes, shared by business & saler portals
|--------------------------------------------------------------------------
*/
$sellerProductRoutes = function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::post('/products/{product}/submit', [ProductController::class, 'submit'])->name('products.submit');
    Route::post('/products/{product}/publish', [ProductController::class, 'publish'])->name('products.publish');
    Route::post('/products/{product}/unpublish', [ProductController::class, 'unpublish'])->name('products.unpublish');
    Route::patch('/products/{product}/toggle-publish', [ProductController::class, 'togglePublish'])->name('products.toggle-publish');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
};

$salerAppointmentRoutes = function () {
    Route::get('/appointments', [ProductVerificationController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [ProductVerificationController::class, 'store'])->name('appointments.store');
};

$salerWarehouseRoutes = function () {
    Route::get('/warehouse', [WarehouseDepositController::class, 'index'])->name('warehouse.index');
    Route::post('/warehouse/deposit', [WarehouseDepositController::class, 'store'])->name('warehouse.deposit');
};

$messagingRoutes = function () {
    Route::get('/messages', [ChatController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [ChatController::class, 'show'])->name('chat.show');

    Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportTicketController::class, 'store'])->middleware('throttle:10,1')->name('support.store');
    Route::get('/support/{ticket}', [SupportTicketController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('support.reply');
};

$sellerOrderRoutes = function () {
    Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{vendorOrder}', [SellerOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{vendorOrder}/status', [SellerOrderController::class, 'updateStatus'])->name('orders.status');
};

$sellerInventoryRoutes = function () {
    Route::get('/inventory', [SellerInventoryController::class, 'index'])->name('inventory.index');
};

$sellerReportRoutes = function () {
    Route::get('/reports', [SellerReportController::class, 'index'])->name('reports.index');
};

$sellerPaymentRoutes = function () {
    Route::get('/payment-settings', [VendorPaymentSettingController::class, 'edit'])->name('payment-settings.edit');
    Route::post('/payment-settings', [VendorPaymentSettingController::class, 'update'])->name('payment-settings.update');

    Route::get('/payment-verifications', [SellerPaymentVerificationController::class, 'index'])->name('payment-verifications.index');
    Route::post('/payment-verifications/{submission}/verify', [SellerPaymentVerificationController::class, 'verify'])->name('payment-verifications.verify');
    Route::post('/payment-verifications/{submission}/reject', [SellerPaymentVerificationController::class, 'reject'])->name('payment-verifications.reject');
    Route::get('/payment-verifications/{submission}/proof', [SellerPaymentVerificationController::class, 'proof'])->name('payment-verifications.proof');
    Route::post('/vendor-orders/{vendorOrder}/collect-cod', [SellerPaymentVerificationController::class, 'collectCod'])->name('vendor-orders.collect-cod');

    Route::get('/refunds', [SellerRefundController::class, 'index'])->name('refunds.index');
    Route::post('/refunds/{refund}/approve', [SellerRefundController::class, 'approve'])->name('refunds.approve');
    Route::post('/refunds/{refund}/reject', [SellerRefundController::class, 'reject'])->name('refunds.reject');

    Route::get('/invoices', [SellerInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [SellerInvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [SellerInvoiceController::class, 'download'])->name('invoices.download');

    Route::get('/reviews', [SellerReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/reply', [SellerReviewController::class, 'reply'])->name('reviews.reply');

    Route::get('/payouts', [SellerWalletController::class, 'index'])->name('payouts.index');
    Route::post('/payouts', [SellerWalletController::class, 'requestPayout'])->name('payouts.request');
};

/*
|--------------------------------------------------------------------------
| Onboarding (business/saler profile completion after registration)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/profile', [OnboardingController::class, 'profile'])->name('profile');
    Route::post('/profile', [OnboardingController::class, 'updateProfile'])->name('profile.store');
});

/*
|--------------------------------------------------------------------------
| Portal shells, one per role
|--------------------------------------------------------------------------
| Real controllers land phase by phase; for now each portal renders its
| dashboard shell so the layout, nav, and role guard are all exercised.
*/
Route::middleware(['auth', 'verified', 'role:'.UserRole::Admin->value])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.status');
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');

        Route::redirect('/settings', '/admin/settings/logo');

        Route::get('/settings/branding', fn () => redirect()->route('admin.settings.logo'))->name('settings.branding');
        Route::post('/settings/branding', [SettingsController::class, 'updateBranding'])->name('settings.branding.update');

        Route::get('/settings/logo', [SettingsController::class, 'logo'])->name('settings.logo');
        Route::post('/settings/logo', [SettingsController::class, 'updateLogo'])->name('settings.logo.update');
        Route::delete('/settings/logo', [SettingsController::class, 'removeLogo'])->name('settings.logo.remove');

        Route::get('/settings/siteicon', [SettingsController::class, 'siteicon'])->name('settings.siteicon');
        Route::post('/settings/siteicon', [SettingsController::class, 'updateSiteicon'])->name('settings.siteicon.update');
        Route::delete('/settings/siteicon', [SettingsController::class, 'removeSiteicon'])->name('settings.siteicon.remove');

        Route::get('/settings/font', [SettingsController::class, 'font'])->name('settings.font');
        Route::post('/settings/font', [SettingsController::class, 'updateFont'])->name('settings.font.update');

        Route::get('/settings/color', [SettingsController::class, 'color'])->name('settings.color');
        Route::post('/settings/color', [SettingsController::class, 'updateColor'])->name('settings.color.update');

        Route::get('/settings/cache', [SettingsController::class, 'cache'])->name('settings.cache');
        Route::post('/settings/cache/{action?}', [SettingsController::class, 'clearCache'])->name('settings.cache.clear');

        Route::get('/settings/mail', [SettingsController::class, 'mail'])->name('settings.mail');
        Route::post('/settings/mail', [SettingsController::class, 'updateMail'])->name('settings.mail.update');
        Route::post('/settings/mail/test', [SettingsController::class, 'sendTestMail'])->name('settings.mail.test');

        Route::get('/settings/storage', [SettingsController::class, 'storage'])->name('settings.storage');
        Route::post('/settings/storage', [SettingsController::class, 'updateStorage'])->name('settings.storage.update');
        Route::post('/settings/storage/test', [SettingsController::class, 'testStorage'])->name('settings.storage.test');

        Route::get('/settings/system', [MaintenanceController::class, 'index'])->name('settings.system');
        Route::post('/settings/system/{action}', [MaintenanceController::class, 'run'])->name('settings.system.run');

        Route::get('/settings/payments', [SettingsController::class, 'payments'])->name('settings.payments');
        Route::post('/settings/payments', [SettingsController::class, 'updatePayments'])->name('settings.payments.update');

        Route::get('/verifications', [AdminVerificationController::class, 'index'])->name('verifications.index');
        Route::get('/verifications/{verification}', [AdminVerificationController::class, 'show'])->name('verifications.show');
        Route::post('/verifications/{verification}/approve', [AdminVerificationController::class, 'approve'])->name('verifications.approve');
        Route::post('/verifications/{verification}/reject', [AdminVerificationController::class, 'reject'])->name('verifications.reject');
        Route::get('/verification-documents/{document}', [AdminVerificationController::class, 'viewDocument'])->name('verification-documents.show');

        Route::get('/verification-requirements', [VerificationRequirementController::class, 'index'])->name('verification-requirements.index');
        Route::post('/verification-requirements', [VerificationRequirementController::class, 'store'])->name('verification-requirements.store');
        Route::patch('/verification-requirements/{verificationRequirement}/toggle', [VerificationRequirementController::class, 'toggle'])->name('verification-requirements.toggle');
        Route::delete('/verification-requirements/{verificationRequirement}', [VerificationRequirementController::class, 'destroy'])->name('verification-requirements.destroy');

        // Category Builder Workspace
        Route::get('/categories/builder', [CategoryBuilderController::class, 'categories'])->name('categories.builder');
        Route::get('/categories/builder/categories', [CategoryBuilderController::class, 'categories'])->name('categories.builder.categories');
        Route::get('/categories/builder/attribute-groups', [CategoryBuilderController::class, 'attributeGroups'])->name('categories.builder.attribute-groups');
        Route::get('/categories/builder/attributes', [CategoryBuilderController::class, 'attributes'])->name('categories.builder.attributes');
        Route::get('/categories/builder/assign', [CategoryBuilderController::class, 'assign'])->name('categories.builder.assign');

        // Categories & Category Attributes
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::put('/categories/{category}/attributes', [CategoryController::class, 'updateAttributes'])->name('categories.attributes.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/categories/{category}/attributes', [CategoryAttributeController::class, 'index'])->name('categories.attributes');
        Route::post('/categories/{category}/attributes', [CategoryAttributeController::class, 'store'])->name('categories.attributes.store');
        Route::post('/categories/{category}/attributes/bulk', [CategoryAttributeController::class, 'bulkStore'])->name('categories.attributes.bulk-store');
        Route::post('/categories/{category}/attributes/sync', [CategoryAttributeController::class, 'sync'])->name('categories.attributes.sync');
        Route::put('/categories/{category}/attributes/{attribute}', [CategoryAttributeController::class, 'update'])->name('categories.attributes.update-pivot');
        Route::delete('/categories/{category}/attributes/{attribute}', [CategoryAttributeController::class, 'destroy'])->name('categories.attributes.destroy');

        // Brands
        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

        // Attribute Groups
        Route::resource('attribute-groups', AttributeGroupController::class);
        Route::patch('/attribute-groups/{attributeGroup}/toggle-active', [AttributeGroupController::class, 'toggleActive'])->name('attribute-groups.toggle-active');

        // Attributes & Values
        Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
        Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
        Route::get('/attributes/{attribute}', [AttributeController::class, 'show'])->name('attributes.show');
        Route::put('/attributes/{attribute}', [AttributeController::class, 'update'])->name('attributes.update');
        Route::patch('/attributes/{attribute}/toggle-active', [AttributeController::class, 'toggleActive'])->name('attributes.toggle-active');
        Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy'])->name('attributes.destroy');
        Route::post('/attributes/{attribute}/values', [AttributeValueController::class, 'store'])->name('attributes.values.store');
        Route::delete('/attributes/{attribute}/values/{value}', [AttributeValueController::class, 'destroy'])->name('attributes.values.destroy');

        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::post('/products/{product}/publish', [AdminProductController::class, 'publish'])->name('products.publish');
        Route::post('/products/{product}/unpublish', [AdminProductController::class, 'unpublish'])->name('products.unpublish');
        Route::patch('/products/{product}/toggle-publish', [AdminProductController::class, 'togglePublish'])->name('products.toggle-publish');
        Route::post('/products/{product}/approve', [AdminProductController::class, 'approve'])->name('products.approve');
        Route::post('/products/{product}/reject', [AdminProductController::class, 'reject'])->name('products.reject');
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

        Route::get('/verification-locations', [VerificationLocationController::class, 'index'])->name('verification-locations.index');
        Route::post('/verification-locations', [VerificationLocationController::class, 'store'])->name('verification-locations.store');
        Route::put('/verification-locations/{verificationLocation}', [VerificationLocationController::class, 'update'])->name('verification-locations.update');
        Route::delete('/verification-locations/{verificationLocation}', [VerificationLocationController::class, 'destroy'])->name('verification-locations.destroy');

        Route::get('/verifiers', [VerifierController::class, 'index'])->name('verifiers.index');
        Route::post('/verifiers', [VerifierController::class, 'store'])->name('verifiers.store');
        Route::patch('/verifiers/{verifierProfile}/location', [VerifierController::class, 'assignLocation'])->name('verifiers.assign-location');

        Route::get('/verification-checklists', [VerificationChecklistController::class, 'index'])->name('verification-checklists.index');
        Route::post('/verification-checklists', [VerificationChecklistController::class, 'store'])->name('verification-checklists.store');
        Route::delete('/verification-checklists/{verificationChecklist}', [VerificationChecklistController::class, 'destroy'])->name('verification-checklists.destroy');

        Route::get('/warehouses', [AdminWarehouseController::class, 'index'])->name('warehouses.index');
        Route::post('/warehouses', [AdminWarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/warehouses/{warehouse}', [AdminWarehouseController::class, 'show'])->name('warehouses.show');
        Route::put('/warehouses/{warehouse}', [AdminWarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('/warehouses/{warehouse}', [AdminWarehouseController::class, 'destroy'])->name('warehouses.destroy');
        Route::post('/warehouses/{warehouse}/locations', [WarehouseLocationController::class, 'store'])->name('warehouses.locations.store');
        Route::delete('/warehouses/{warehouse}/locations/{location}', [WarehouseLocationController::class, 'destroy'])->name('warehouses.locations.destroy');

        Route::get('/warehouse-products', [WarehouseProductController::class, 'index'])->name('warehouse-products.index');
        Route::get('/warehouse-products/{warehouseProduct}/receive', [WarehouseProductController::class, 'receive'])->name('warehouse-products.receive');
        Route::post('/warehouse-products/{warehouseProduct}/receive', [WarehouseProductController::class, 'storeReceive'])->name('warehouse-products.receive.store');
        Route::post('/warehouse-products/{warehouseProduct}/release', [WarehouseProductController::class, 'release'])->name('warehouse-products.release');

        Route::get('/subscriptions/plans', [SubscriptionPlanController::class, 'index'])->name('subscriptions.plans.index');
        Route::post('/subscriptions/plans', [SubscriptionPlanController::class, 'store'])->name('subscriptions.plans.store');
        Route::put('/subscriptions/plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'update'])->name('subscriptions.plans.update');
        Route::delete('/subscriptions/plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'destroy'])->name('subscriptions.plans.destroy');

        Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');

        Route::get('/payment-verifications', [AdminPaymentVerificationController::class, 'index'])->name('payment-verifications.index');
        Route::post('/payment-verifications/{submission}/verify', [AdminPaymentVerificationController::class, 'verify'])->name('payment-verifications.verify');
        Route::post('/payment-verifications/{submission}/reject', [AdminPaymentVerificationController::class, 'reject'])->name('payment-verifications.reject');
        Route::get('/payment-verifications/{submission}/proof', [AdminPaymentVerificationController::class, 'proof'])->name('payment-verifications.proof');
        Route::post('/vendor-orders/{vendorOrder}/collect-cod', [AdminPaymentVerificationController::class, 'collectCod'])->name('vendor-orders.collect-cod');

        Route::get('/refunds', [AdminRefundController::class, 'index'])->name('refunds.index');
        Route::post('/refunds/{refund}/approve', [AdminRefundController::class, 'approve'])->name('refunds.approve');
        Route::post('/refunds/{refund}/reject', [AdminRefundController::class, 'reject'])->name('refunds.reject');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');

        Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/download', [AdminInvoiceController::class, 'download'])->name('invoices.download');

        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('/reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');

        Route::get('/review-reports', [AdminReviewReportController::class, 'index'])->name('review-reports.index');
        Route::post('/review-reports/{report}/resolve', [AdminReviewReportController::class, 'resolve'])->name('review-reports.resolve');
        Route::post('/review-reports/{report}/dismiss', [AdminReviewReportController::class, 'dismiss'])->name('review-reports.dismiss');

        Route::get('/chat', [AdminChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/{conversation}', [AdminChatController::class, 'show'])->name('chat.show');

        Route::get('/support', [AdminSupportController::class, 'index'])->name('support.index');
        Route::get('/support/{ticket}', [AdminSupportController::class, 'show'])->name('support.show');
        Route::post('/support/{ticket}/reply', [AdminSupportController::class, 'reply'])->name('support.reply');
        Route::post('/support/{ticket}/status', [AdminSupportController::class, 'updateStatus'])->name('support.status');
        Route::post('/support/{ticket}/assign', [AdminSupportController::class, 'assign'])->name('support.assign');

        Route::get('/commission-rules', [CommissionRuleController::class, 'index'])->name('commission-rules.index');
        Route::post('/commission-rules', [CommissionRuleController::class, 'store'])->name('commission-rules.store');
        Route::put('/commission-rules/{commissionRule}', [CommissionRuleController::class, 'update'])->name('commission-rules.update');
        Route::delete('/commission-rules/{commissionRule}', [CommissionRuleController::class, 'destroy'])->name('commission-rules.destroy');

        Route::get('/payouts', [AdminPayoutController::class, 'index'])->name('payouts.index');
        Route::post('/payouts/{payout}/approve', [AdminPayoutController::class, 'approve'])->name('payouts.approve');
        Route::post('/payouts/{payout}/processing', [AdminPayoutController::class, 'markProcessing'])->name('payouts.processing');
        Route::post('/payouts/{payout}/complete', [AdminPayoutController::class, 'complete'])->name('payouts.complete');
        Route::post('/payouts/{payout}/reject', [AdminPayoutController::class, 'reject'])->name('payouts.reject');

        Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');

        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');

        Route::get('/blog', [AdminPostController::class, 'index'])->name('blog.index');
        Route::get('/blog/create', [AdminPostController::class, 'create'])->name('blog.create');
        Route::post('/blog', [AdminPostController::class, 'store'])->name('blog.store');
        Route::get('/blog/{post}/edit', [AdminPostController::class, 'edit'])->name('blog.edit');
        Route::put('/blog/{post}', [AdminPostController::class, 'update'])->name('blog.update');
        Route::delete('/blog/{post}', [AdminPostController::class, 'destroy'])->name('blog.destroy');

        Route::get('/blog-categories', [BlogCategoryController::class, 'index'])->name('blog-categories.index');
        Route::post('/blog-categories', [BlogCategoryController::class, 'store'])->name('blog-categories.store');
        Route::put('/blog-categories/{blogCategory}', [BlogCategoryController::class, 'update'])->name('blog-categories.update');
        Route::delete('/blog-categories/{blogCategory}', [BlogCategoryController::class, 'destroy'])->name('blog-categories.destroy');

        Route::get('/pages', [AdminPageController::class, 'index'])->name('pages.index');
        Route::get('/pages/create', [AdminPageController::class, 'create'])->name('pages.create');
        Route::post('/pages', [AdminPageController::class, 'store'])->name('pages.store');
        Route::get('/pages/{page}/edit', [AdminPageController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{page}', [AdminPageController::class, 'destroy'])->name('pages.destroy');

        Route::get('/banners', [AdminBannerController::class, 'index'])->name('banners.index');
        Route::post('/banners', [AdminBannerController::class, 'store'])->name('banners.store');
        Route::put('/banners/{banner}', [AdminBannerController::class, 'update'])->name('banners.update');
        Route::delete('/banners/{banner}', [AdminBannerController::class, 'destroy'])->name('banners.destroy');

        Route::resource('social-types', SocialTypeController::class)->except(['create', 'show', 'edit']);
        Route::get('/visitor-reports', [VisitorReportController::class, 'index'])->name('visitor-reports.index');
    });

Route::middleware(['auth', 'verified', 'role:'.UserRole::Business->value, 'profile.complete'])
    ->prefix('business')->name('business.')->group(function () use ($sellerProductRoutes, $sellerPaymentRoutes, $sellerOrderRoutes, $sellerInventoryRoutes, $sellerReportRoutes, $messagingRoutes) {
        Route::view('/', 'business.dashboard')->name('dashboard');

        Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
        Route::post('/verification', [VerificationController::class, 'store'])->name('verification.store');

        Route::get('/store', [StoreSettingsController::class, 'edit'])->name('store.edit');
        Route::post('/store', [StoreSettingsController::class, 'updateBusiness'])->name('store.update');

        Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
        Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
        Route::post('/subscription/{plan}', [SubscriptionController::class, 'store'])->name('subscription.store');

        $sellerProductRoutes();
        $sellerPaymentRoutes();
        $sellerOrderRoutes();
        $sellerInventoryRoutes();
        $sellerReportRoutes();
        $messagingRoutes();
    });

Route::middleware(['auth', 'verified', 'role:'.UserRole::Saler->value, 'profile.complete'])
    ->prefix('saler')->name('saler.')->group(function () use ($sellerProductRoutes, $salerAppointmentRoutes, $salerWarehouseRoutes, $sellerPaymentRoutes, $sellerOrderRoutes, $sellerInventoryRoutes, $sellerReportRoutes, $messagingRoutes) {
        Route::view('/', 'saler.dashboard')->name('dashboard');

        Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
        Route::post('/verification', [VerificationController::class, 'store'])->name('verification.store');

        Route::get('/store', [StoreSettingsController::class, 'edit'])->name('store.edit');
        Route::post('/store', [StoreSettingsController::class, 'updateSaler'])->name('store.update');

        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
        Route::post('/subscriptions/{plan}', [SubscriptionController::class, 'store'])->name('subscriptions.store');

        $sellerProductRoutes();
        $salerAppointmentRoutes();
        $salerWarehouseRoutes();
        $sellerPaymentRoutes();
        $sellerOrderRoutes();
        $sellerInventoryRoutes();
        $sellerReportRoutes();
        $messagingRoutes();
    });

Route::middleware(['auth', 'verified', 'role:'.UserRole::Verifier->value])
    ->prefix('verifier')->name('verifier.')->group(function () {
        Route::view('/', 'verifier.dashboard')->name('dashboard');

        Route::get('/appointments', [VerifierAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/{verification}/inspect', [VerifierAppointmentController::class, 'inspect'])->name('appointments.inspect');
        Route::post('/appointments/{verification}/submit', [VerifierAppointmentController::class, 'submit'])->name('appointments.submit');
    });

Route::middleware(['auth', 'verified', 'role:'.UserRole::Customer->value])
    ->prefix('account')->name('account.')->group(function () use ($messagingRoutes) {
        Route::get('/', [CustomerAccountController::class, 'index'])->name('dashboard');

        Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');

        Route::post('/vendor-orders/{vendorOrder}/payment-proof', [ManualPaymentSubmissionController::class, 'store'])->name('payment-proof.store');
        Route::post('/vendor-orders/{vendorOrder}/refund-request', [RefundRequestController::class, 'store'])->middleware('throttle:10,1')->name('refund-requests.store');

        Route::get('/invoices', [CustomerInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [CustomerInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/download', [CustomerInvoiceController::class, 'download'])->name('invoices.download');

        Route::get('/reviews', [CustomerReviewController::class, 'index'])->name('reviews.index');
        Route::post('/orders/{order}/reviews', [CustomerReviewController::class, 'store'])->middleware('throttle:10,1')->name('reviews.store');

        Route::get('/wishlist', [CustomerWishlistController::class, 'index'])->name('wishlist.index');

        Route::get('/addresses', [CustomerAddressController::class, 'index'])->name('addresses.index');
        Route::post('/addresses', [CustomerAddressController::class, 'store'])->name('addresses.store');
        Route::patch('/addresses/{address}', [CustomerAddressController::class, 'update'])->name('addresses.update');
        Route::delete('/addresses/{address}', [CustomerAddressController::class, 'destroy'])->name('addresses.destroy');
        Route::post('/addresses/{address}/default', [CustomerAddressController::class, 'setDefault'])->name('addresses.set-default');

        Route::get('/returns', [CustomerReturnController::class, 'index'])->name('returns.index');

        Route::get('/security', [CustomerSecurityController::class, 'edit'])->name('security.edit');
        Route::patch('/security/password', [CustomerSecurityController::class, 'updatePassword'])->name('security.password');

        $messagingRoutes();
    });

Route::middleware('auth')->group(function () {
    Route::get('/account/profile', [CustomerProfileController::class, 'edit'])->name('account.profile.edit');
    Route::patch('/account/profile', [CustomerProfileController::class, 'update'])->name('account.profile.update');
});

require __DIR__.'/auth.php';
