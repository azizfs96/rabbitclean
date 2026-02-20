<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Contracts\Role;
use App\Http\Controllers\FCMController;
use App\Http\Controllers\CreateSuperAdmin;
use App\Http\Controllers\Web\PosController;
use App\Http\Controllers\Web\AreaController;
use App\Http\Controllers\Web\ServiceAreaController;
use App\Http\Controllers\Web\LanguageController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Root\AdminController;
use App\Http\Controllers\Web\WebSettingController;
use App\Http\Controllers\Web\OtpSettingController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\InvoiceManageController;
use App\Http\Controllers\Web\OrderScheduleController;
use App\Http\Controllers\Web\Social\SocialController;
use App\Http\Controllers\NotificationManageController;
use App\Http\Controllers\Web\Banners\BannerController;
use App\Http\Controllers\Web\PaymentGatewayController;
use App\Http\Controllers\Web\Products\OrderController;
use App\Http\Controllers\Web\Products\CouponController;
use App\Http\Controllers\Web\Profile\ProfileController;
use App\Http\Controllers\Web\Setting\SettingController;
use App\Http\Controllers\Web\SMSGatewaySetupController;
use App\Http\Controllers\Web\Contacts\ContactController;
use App\Http\Controllers\Web\Products\ProductController;
use App\Http\Controllers\Web\Revenues\RevenueController;
use App\Http\Controllers\Web\Services\ServiceController;
use App\Http\Controllers\Web\Variants\VariantController;
use App\Http\Controllers\Web\MailConfigurationController;
use App\Http\Controllers\Web\Customers\CustomerController;
use App\Http\Controllers\Web\Products\SubProductController;
use App\Http\Controllers\Web\MobileAppUrl\MobileAppUrlController;
use App\Http\Controllers\Web\Services\AdditionalServiceController;
use App\Http\Controllers\Web\OrderReviewController;
use App\Http\Controllers\API\Order\OrderController as ApiOrderController;
use App\Http\Controllers\Web\Subscription\SubscriptionController as WebSubscriptionController;
use App\Http\Controllers\Web\Subscription\CustomerSubscriptionController;

/*
+--------------------------------------------------------------------------
+ Web Routes
+--------------------------------------------------------------------------
*/



Route::get('privacy-policy', [LoginController::class, 'privacyPolicy'])->name('privacy.policy');
Route::get('terms-condition', [LoginController::class, 'termsCondition'])->name('terms.condition');

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:admin|visitor|root', 'permission_check'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('root');

    // Service routes
    Route::get('/services', [ServiceController::class, 'index'])->name('service.index');
    Route::get('/services/create', [ServiceController::class, 'create'])->name('service.create');
    Route::post('/services', [ServiceController::class, 'store'])->name('service.store');
    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('service.edit');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('service.update');
    Route::get('/services/{service}/toggle-status', [ServiceController::class, 'toggleActivationStatus'])
        ->name('service.status.toggle');
    Route::get('/services/{service}/variants', [ServiceController::class, 'getVariant'])->name('service.getVariant');

    // Additional service
    Route::get('/additional-services', [AdditionalServiceController::class, 'index'])->name('additional.index');
    Route::get('/additional-services/create', [AdditionalServiceController::class, 'create'])->name('additional.create');
    Route::post('/additional-services', [AdditionalServiceController::class, 'store'])->name('additional.store');
    Route::get('/additional-services/{additional}/edit', [AdditionalServiceController::class, 'edit'])->name('additional.edit');
    Route::put('/additional-services/{additional}', [AdditionalServiceController::class, 'update'])->name('additional.update');
    Route::get('/additional-services/{additional}/toggle-status', [AdditionalServiceController::class, 'toggleActivationStatus'])
        ->name('additional.status.toggle');

    // Variant routes
    Route::get('/variants', [VariantController::class, 'index'])->name('variant.index');
    Route::put('/variants/{variant}/', [VariantController::class, 'update'])->name('variant.update');
    Route::post('/variants', [VariantController::class, 'store'])->name('variant.store');
    Route::get('/variants/{variant}/products', [VariantController::class, 'productsVariant'])->name('variant.products');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notification.index');
    Route::post('/send-notifications', [NotificationController::class, 'SendNotification'])->name('notification.send');

    // Customer routes
    Route::get('/customers', [CustomerController::class, 'index'])->name('customer.index');
    Route::get('/customers/{customer}/show', [CustomerController::class, 'show'])->name('customer.show');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customer.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customer.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customer.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customer.update');

    Route::get('/get-customer-addresses/{customerId}', [CustomerController::class, 'getCustomerAddresses']);

    Route::get('/user/{user}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('user.status.toggle');

    // Product routes
    Route::controller(ProductController::class)->group(function () {
        Route::get('/products', 'index')->name('product.index');
        Route::get('/products/create', 'create')->name('product.create');
        Route::post('/products', 'store')->name('product.store');
        Route::get('/products/{product}/show', 'show')->name('product.show');
        Route::get('/products/{product}/edit', 'edit')->name('product.edit');
        Route::put('/products/{product}/update', 'update')->name('product.update');
        Route::get('/products/{product}/delete', 'delete')->name('product.delete');
        Route::get('/products/{product}/toggle-status', 'toggleActivationStatus')->name('product.status.toggle');
        Route::put('/products/{product}/ordering', 'orderUpdate')->name('product.update.order');
    });

    Route::controller(SubProductController::class)->group(function () {
        Route::get('/products/{product}/sub-product', 'index')->name('product.subproduct.index');
        Route::post('/products/{product}/sub-product/store', 'store')->name('product.subproduct.store');
        Route::put('/products/{product}/sub-product/update', 'update')->name('product.subproduct.update');
    });

    // Banner Routes
    Route::get('/web-banners', [BannerController::class, 'index'])->name('banner.index');
    Route::get('/mobile-banners', [BannerController::class, 'getPromotional'])->name('banner.promotional');
    Route::post('/banners', [BannerController::class, 'store'])->name('banner.store');
    Route::get('/banners/{banner}/edit', [BannerController::class, 'edit'])->name('banner.edit');
    Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('banner.update');
    Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banner.destroy');
    Route::get('/banners/{banner}/toggle-status', [BannerController::class, 'toggleActivationStatus'])
        ->name('banner.status.toggle');

    // Order Routes
    Route::get('/orders', [OrderController::class, 'index'])->name('order.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('order.show');
    Route::get('/orders/{order}/update-status', [OrderController::class, 'statusUpdate'])->name('order.status.change');
    Route::post('/orders/{order}/update-products', [OrderController::class, 'updateProducts'])->name('order.products.update');
    Route::post('/orders/{order}/send-to-customer', [OrderController::class, 'sendToCustomer'])->name('order.send.customer');
    Route::get('/orders/{order}/print/labels', [OrderController::class, 'printLabels'])
        ->name('order.print.labels');
    Route::get('/orders/{order}/print/invoice', [OrderController::class, 'printInvioce'])
        ->name('order.print.invioce');

    //INcomplete Order Route
    Route::get('/orders-incomplete', [OrderController::class, 'index'])->name('orderIncomplete.index');
    Route::get('/orders/{order}/paid', [OrderController::class, 'orderPaid'])->name('orderIncomplete.paid');

    // Revenue Eoutes
    Route::get('revenues', [RevenueController::class, 'index'])->name('revenue.index');
    Route::get('revenues/generate-pdf', [RevenueController::class, 'generatePDF'])->name('revenue.generate.pdf');
    Route::get('reports/generate-pdf', [RevenueController::class, 'generateInvoicePDF'])->name('report.generate.pdf');

    // Coupon Routes
    Route::get('/coupons', [CouponController::class, 'index'])->name('coupon.index');
    Route::get('/coupons/create', [CouponController::class, 'create'])->name('coupon.create');
    Route::post('/coupons', [CouponController::class, 'store'])->name('coupon.store');
    Route::get('/coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('coupon.edit');
    Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('coupon.update');

    //Contact Routes
    Route::get('/contacts', [ContactController::class, 'index'])->name('contact');

    //Profile
    Route::get('/setting/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/setting/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/setting/profile-edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/setting/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::get('/setting/profile/change-password', function () {
        return view('profile.change-password');
    })->name('profile.change-password');

    Route::controller(OrderScheduleController::class)->group(function () {
        Route::get('/{type}/scheduls', 'index')->name('schedule.index');
        Route::get('/schedules/{id}/toggle/update', 'updateStatus')->name('toggole.status.update');
        Route::put('/schedules/{orderSchedule}/update', 'update')->name('schedule.update');
    });

    // Subscription Management Routes
    Route::controller(WebSubscriptionController::class)->group(function () {
        Route::get('/subscriptions', 'index')->name('subscription.index');
        Route::get('/subscriptions/create', 'create')->name('subscription.create');
        Route::post('/subscriptions', 'store')->name('subscription.store');
        Route::get('/subscriptions/{subscription}/edit', 'edit')->name('subscription.edit');
        Route::put('/subscriptions/{subscription}', 'update')->name('subscription.update');
        Route::get('/subscriptions/{subscription}/toggle', 'toggle')->name('subscription.toggle');
        Route::delete('/subscriptions/{subscription}', 'destroy')->name('subscription.destroy');
        Route::post('/subscriptions/update-order', 'updateOrder')->name('subscription.update-order');
    });

    // Customer Subscription Management Routes
    Route::controller(CustomerSubscriptionController::class)->group(function () {
        Route::get('/customer-subscriptions', 'index')->name('customer-subscription.index');
        Route::get('/customer-subscriptions/create', 'createForm')->name('customer-subscription.create');
        Route::post('/customer-subscriptions/assign', 'assignSubscription')->name('customer-subscription.assign');
        Route::get('/customer-subscriptions/{customerSubscription}', 'show')->name('customer-subscription.show');
        Route::post('/customer-subscriptions/{customerSubscription}/adjust-credits', 'adjustCredits')->name('customer-subscription.adjust-credits');
        Route::post('/customer-subscriptions/{customerSubscription}/extend', 'extend')->name('customer-subscription.extend');
        Route::post('/customer-subscriptions/{customerSubscription}/cancel', 'cancel')->name('customer-subscription.cancel');
        Route::post('/customer-subscriptions/{customerSubscription}/renew', 'renew')->name('customer-subscription.renew');
    });
});

// access only root user.
Route::middleware(['auth', 'role:root|visitor'])->group(function () {
    // Settings Routes
    Route::get('/settings/{slug}', [SettingController::class, 'show'])->name('setting.show');
    Route::get('/settings/{slug}/edit', [SettingController::class, 'edit'])->name('setting.edit');
    Route::put('/settings/{setting}', [SettingController::class, 'update'])->name('setting.update');

    //Mobile App Link
    Route::get('/setting/mobile-app-link', [MobileAppUrlController::class, 'index'])->name('mobileApp');
    Route::post('/setting/mobile-app-link', [MobileAppUrlController::class, 'updateOrCreate'])->name('mobileApp');

    //Social link
    Route::get('/setting/social-link', [SocialController::class, 'index'])->name('socialLink.index');
    Route::post('/setting/social-link', [SocialController::class, 'store'])->name('socialLink.store');
    Route::post('/setting/social-link/{socialLink}', [SocialController::class, 'update'])->name('socialLink.update');
    Route::get('/setting/social-link/{socialLink}/delete', [SocialController::class, 'delete'])->name('socialLink.delete');

    Route::controller(LanguageController::class)->group(function () {
        Route::get('/language', 'index')->name('language.index');
        Route::get('/language/create', 'create')->name('language.create');
        Route::post('/language/store', 'store')->name('language.store');
        Route::get('/language/{language}/edit', 'edit')->name('language.edit');
        Route::put('/language/{language}/update', 'update')->name('language.update');
        Route::get('/language/{language}/delete', 'delete')->name('language.delete');
    });



    Route::get('/web-setting', [WebSettingController::class, 'index'])->name(('webSetting.index'));
    Route::post('/web-setting/{webSetting?}', [WebSettingController::class, 'update'])->name(('webSetting.update'));

    Route::get('/otp-setting', [OtpSettingController::class, 'index'])->name(('otpSetting.index'));
    Route::post('/otp-setting', [OtpSettingController::class, 'update'])->name(('otpSetting.update'));

    Route::get('/invoice-manage', [InvoiceManageController::class, 'index'])->name(('invoiceManage.index'));
    Route::post('/invoice-manage/{invoiceManage?}', [InvoiceManageController::class, 'update'])->name(('invoiceManage.update'));

    Route::get('/customer/{customer}/delete', [CustomerController::class, 'delete'])->name('customer.delete');

    Route::controller(AdminController::class)->group(function () {
        Route::get('/admins', 'index')->name('admin.index');
        Route::get('/admins/{user}/toggle-status-update', 'toggleStatusUpdate')->name('admin.status-update');
        Route::get('/admins/create', 'create')->name('admin.create');
        Route::post('/admins', 'store')->name('admin.store');
        Route::get('/admins/{user}/edit', 'edit')->name('admin.edit');
        Route::put('/admins/{user}', 'update')->name('admin.update');
        Route::get('/admins/{user}/show', 'show')->name('admin.show');
        Route::post('/admins/{user}/set-permission', 'setPermission')->name('admin.set-permission');
    });

    // Area Route
    Route::controller(AreaController::class)->group(function () {
        Route::get('/areas', 'index')->name('areas.index');
        Route::post('/areas/store', 'store')->name('areas.store');
        Route::put('/areas/{area}/update', 'update')->name('areas.update');
        Route::get('/areas/{area}/toggle', 'toggle')->name('areas.toggle');
        Route::get('/areas/{area}/delete', 'delete')->name('areas.delete');
    });

    // أحياء الخدمة (Service Areas) - للتطبيق وفحص نطاق الخدمة
    Route::controller(ServiceAreaController::class)->group(function () {
        Route::get('/service-areas', 'index')->name('service-areas.index');
        Route::post('/service-areas/store', 'store')->name('service-areas.store');
        Route::put('/service-areas/{service_area}/update', 'update')->name('service-areas.update');
        Route::get('/service-areas/{service_area}/toggle', 'toggle')->name('service-areas.toggle');
        Route::get('/service-areas/{service_area}/delete', 'delete')->name('service-areas.delete');
    });

    //  Msegat SMS Gateway (Configuration & Testing)
    Route::controller(SMSGatewaySetupController::class)->group(function () {
        Route::get('/sms-gateway', 'index')->name('sms-gateway.index');
        Route::put('/sms-gateway', 'update')->name('sms-gateway.update');
        Route::post('/sms-gateway/send-test', 'sendTest')->name('sms-gateway.send-test');
        Route::get('/sms-gateway/balance', 'checkBalance')->name('sms-gateway.balance');
    });

    // Notification management
    Route::controller(NotificationManageController::class)->group(function () {
        Route::get('/notifications/manage', 'index')->name('notification.manage');
        Route::post('/notifications/update/{notificationManage}', 'update')->name('notification.manage.update');
    });

    // firebase cloud message
    Route::controller(FCMController::class)->group(function () {
        Route::get('/fcm-configuration', 'index')->name('fcm.index');
        Route::post('/fcm-configuration', 'update')->name('fcm.update');
        Route::get('/fcm-configuration/download', 'download')->name('fcm.download');
        Route::delete('/fcm-configuration', 'delete')->name('fcm.delete');
    });

    //  mail configuration
    Route::controller(MailConfigurationController::class)->group(function () {
        Route::get('/mail-configuration', 'index')->name('mail-config.index');
        Route::put('/mail-configuration', 'update')->name('mail-config.update');
    });

});

Route::get('/new-orders', [ApiOrderController::class, 'newOrder'])->name('new.orders');


// Payment Gateway
Route::controller(PaymentGatewayController::class)->group(function () {
    Route::get('/payment-gateway', 'index')->name('payment-gateway.index');
    Route::post('/payment-gateway/{paymentGateway}/update', 'update')->name('payment-gateway.update');
    Route::get('/payment-gateway/{paymentGateway}/toggle', 'toggle')->name('payment-gateway.toggle');
    Route::post('payment/process/{order}', 'process')->name('payment.process');
});
// pos routes
// Route::middleware(['auth', 'role:vendor|store'])->group(function () {
// pos route
Route::controller(PosController::class)->group(function () {
    Route::get('/pos', 'index')->name('pos.index');
    Route::post('/pos', 'store')->name('pos.store');
    Route::get('/pos/sales', 'sales')->name('pos.sales');
    Route::get('/pos/payment', 'payment')->name('pos.payment');
    Route::post('/pos/customer', 'storeCustomer')->name('pos.customerStore');
    Route::post('/pos/address', 'storeAddress')->name('pos.addressStore');
    Route::get('/pos/sales/{order}/details', 'show')->name('pos.order.show');
    Route::get('/fetch/variants', 'fetchVariants')->name('pos.fetch.variants');
    Route::get('/fetch/products', 'fetchProducts')->name('pos.fetch.products');
});
// });
Route::get('change-language', function () {
    App::setLocale(\request()->ln);
    session()->put('local', \request()->ln);
    session()->save(); // Force session save
    return redirect()->back()->with('language_changed', true);
})->name('change.local');
