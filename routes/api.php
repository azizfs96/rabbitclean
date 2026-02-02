<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\User\UserController;
use App\Http\Controllers\API\Order\OrderController;
use App\Http\Controllers\API\PaymentGatewayController;
use App\Http\Controllers\API\Banner\BannerController;
use App\Http\Controllers\API\Coupon\CouponController;
use App\Http\Controllers\API\Master\masterController;
use App\Http\Controllers\API\Rating\RatingController;
use App\Http\Controllers\API\Address\AddressController;
use App\Http\Controllers\API\Product\ProductController;
use App\Http\Controllers\API\Service\ServiceController;
use App\Http\Controllers\API\Setting\SettingController;
use App\Http\Controllers\API\Variant\VariantController;
use App\Http\Controllers\API\Contacts\ContactController;
use App\Http\Controllers\API\Customers\CustomerController;
use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Promotion\PromotionController;
use App\Http\Controllers\API\Additional\AdditionalServiceController;
use App\Http\Controllers\API\Admin\Auth\LoginController;
use App\Http\Controllers\API\Payment\PaymentController as PaymentControllerApi;
use App\Http\Controllers\API\PostCode\PostCodeController;
use App\Http\Controllers\API\Admin\dashboard\dashboardController;
use App\Http\Controllers\API\Admin\order\OrderController as AdminOrderController;
use App\Http\Controllers\API\Admin\OrderReviewController;
use App\Http\Controllers\API\AreaController;
// use App\Http\Controllers\API\Customers\CardController;
use App\Http\Controllers\API\Notifications\NotificationsController;
use App\Http\Controllers\API\Social\SocialLinkController;
use App\Http\Controllers\API\Order\PosController;
use App\Http\Controllers\API\Subscription\SubscriptionController;
use App\Http\Controllers\API\Subscription\SubscriptionPaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/privacy-policy', function () {
    return view('settings.privacy-policy');
});

Route::middleware('guest:api')->group(function () {
    // OTP-based Login (New Flow)
    Route::post('/login/otp/request', [AuthController::class, 'requestLoginOtp']);
    Route::post('/login/otp/verify', [AuthController::class, 'verifyLoginOtp']);
    Route::post('/profile/complete', [AuthController::class, 'completeProfile']);

    // Legacy endpoints (kept for backward compatibility)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Password reset
    Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
    Route::post('/forgot-password/otp/verify', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
});
Route::get('/social-link', [SocialLinkController::class, 'index']);

Route::controller(AreaController::class)->group(function () {
    Route::get('/areas', 'index');
});

Route::post('/resend/otp', [AuthController::class, 'resendOTP']);

Route::middleware(['auth:api', 'role:customer'])->group(function () {
    Route::post('/coupons/{coupon:code}/apply', [CouponController::class, 'apply']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::get('/orders/{id}/details', [OrderController::class, 'show']);

    Route::post('/users/update', [UserController::class, 'update']);
    Route::post('/users/profile-photo/update', [UserController::class, 'updateProfilePhoto']);
    Route::post('/users/change-password', [UserController::class, 'changePassword']);

    Route::get('/customers', [CustomerController::class, 'show']);

    // Route::get('/card-list', [CardController::class, 'index']);
    // Route::post('/cards', [CardController::class, 'store']);

    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::post('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'delete']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/ratings', [RatingController::class, 'index']);
    Route::post('/ratings', [RatingController::class, 'store']);

    Route::post('/contact/verify', [AuthController::class, 'mobileVerify']);

    Route::get('/pick-schedules/{date}', [OrderController::class, 'pickSchedule']);

    Route::post('/payments', [PaymentControllerApi::class, 'store']);

    Route::get('/notifications', [NotificationsController::class, 'index']);
    Route::post('/notifications', [NotificationsController::class, 'store']);
    Route::post('/notifications/{id}', [NotificationsController::class, 'update']);
    Route::delete('/notifications/{notification}', [NotificationsController::class, 'delete']);

    // Subscription Routes
    Route::get('/my-subscription', [SubscriptionController::class, 'mySubscription']);
    Route::post('/subscriptions/{subscription}/purchase', [SubscriptionController::class, 'purchase']);
    Route::post('/subscriptions/{customerSubscription}/activate', [SubscriptionController::class, 'activate']);
    Route::post('/my-subscription/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/my-subscription/toggle-auto-renew', [SubscriptionController::class, 'toggleAutoRenew']);

    // Credit Routes
    Route::get('/credits/balance', [SubscriptionController::class, 'creditBalance']);
    Route::get('/credits/history', [SubscriptionController::class, 'creditHistory']);

    // Subscription Orders
    Route::get('/subscription/orders', [SubscriptionController::class, 'subscriptionOrders']);

    // Subscription Payment Routes (PayTabs)
    Route::post('/subscriptions/{subscription}/pay', [SubscriptionPaymentController::class, 'initiatePayment']);
    Route::get('/subscriptions/{customerSubscription}/verify-payment', [SubscriptionPaymentController::class, 'verifyPayment']);
});

// Public Subscription Routes
Route::get('/subscriptions', [SubscriptionController::class, 'index']);
Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show']);

// Subscription Payment Callbacks (no auth required - called by PayTabs)
Route::post('/subscription/payment/callback', [SubscriptionPaymentController::class, 'handleCallback'])->name('subscription.payment.callback');
Route::match(['get', 'post'], '/subscription/payment/return', [SubscriptionPaymentController::class, 'handleReturn'])->name('subscription.payment.return');
Route::controller(PosController::class)->group(function () {
    Route::post('/pos', 'posStore');
    Route::get('/pos/customer', 'posCustomer');
    Route::get('/pos/service', 'posService');
    Route::get('/fetch/variants', 'fetchVariants')->name('pos.fetch.variants');
    Route::get('/order/payment', 'payment')->name('order.payment');
    Route::get('/fetch/products', 'fetchProducts')->name('pos.fetch.products');
});
Route::controller(PaymentGatewayController::class)->group(function () {
    Route::post('payment/process/{order}', 'processOrder')->name('payment.process.order');
    Route::get('payment/success', 'success')->name('payment.success');
});

Route::get('/payments/{orderId}/{cardId}', [PaymentController::class, 'index']);

Route::get('/banners', [BannerController::class, 'index']);
Route::get('/promotions', [PromotionController::class, 'index']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/additional-services', [AdditionalServiceController::class, 'index']);
Route::get('/variants', [VariantController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/legal-pages/{page:slug}', [SettingController::class, 'show']);
Route::get('/settings/privacy-policy', [SettingController::class, 'privacyShow']);
Route::get('/settings/trams-of-service', [SettingController::class, 'termsShow']);

Route::post('/contacts', [ContactController::class, 'store']);

Route::get('/master', [masterController::class, 'index']);

Route::get('/post-code', [PostCodeController::class, 'index']);



// =========Route for Admin==========

Route::post('/admin/login', [LoginController::class, 'login']);

Route::group(['prefix' => '/admin', 'middleware' => ['auth:api', 'role:admin']], function () {
    Route::get('/dashboard', [dashboardController::class, 'index']);
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [AdminOrderController::class, 'orderDetails']);
    Route::get('/orders/{order}/update-status', [AdminOrderController::class, 'statusUpdate']);
    Route::get('/orders-status', [dashboardController::class, 'status']);

    // Order Review Management
    Route::get('/orders/pending-review', [OrderReviewController::class, 'pendingOrders']);
    Route::post('/orders/{order}/review', [OrderReviewController::class, 'updateOrderReview']);

    // Review Settings
    Route::get('/review-settings', [OrderReviewController::class, 'getReviewSettings']);
    Route::post('/review-settings', [OrderReviewController::class, 'updateReviewSettings']);

    Route::get('/logout', [LoginController::class, 'logout']);
});

// Payment Gateway
// Route::controller(PaymentGatewayController::class)->group(function () {
//     Route::get('/payment-gateway', 'index')->name('payment-gateway.index');
//     Route::post('payment/process/{order}', 'process')->name('payment.process');
// });
