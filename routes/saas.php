<?php

use App\Http\Controllers\PaymentSubscriptionController;
use App\Http\Controllers\Saas\Admin\ContactMessageController;
use App\Http\Controllers\Saas\OwnerAuthController;
use App\Http\Controllers\Saas\SubscriptionController as SaasSubscriptionController;
use App\Http\Controllers\Saas\Admin\CorePagesController;
use App\Http\Controllers\Saas\Admin\FaqController;
use App\Http\Controllers\Saas\Admin\FeatureController;
use App\Http\Controllers\Saas\Admin\GatewayController;
use App\Http\Controllers\Saas\Admin\HomeSettingController;
use App\Http\Controllers\Saas\Admin\HowItWorkController;
use App\Http\Controllers\Saas\Admin\PackageController;
use App\Http\Controllers\Saas\Admin\PolicyController;
use App\Http\Controllers\Saas\Admin\SubscriptionController;
use App\Http\Controllers\Saas\Admin\TestimonialController;
use App\Http\Controllers\Saas\FrontendController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['version.update', 'addon.update', 'isFrontend']], function () {
    // register owner
    Route::get('owner-register', [OwnerAuthController::class, 'owner_register_form'])->name('owner.register.form');
    Route::post('owner-register', [OwnerAuthController::class, 'owner_register_store'])->name('owner.register.store');

    // policy
    Route::get('terms-conditions', [FrontendController::class, 'termsConditions'])->name('terms-conditions');
    Route::get('privacy-policy', [FrontendController::class, 'privacyPolicy'])->name('privacy-policy');
    Route::get('cookie-policy', [FrontendController::class, 'cookiePolicy'])->name('cookie-policy');

    // contact
    Route::post('contact-message-store', [FrontendController::class, 'contactMessageStore'])->name('contact.message.store');
});

Route::group(['prefix' => 'payment'], function () {
    Route::post('/subscription', [PaymentSubscriptionController::class, 'checkout'])->name('payment.subscription.checkout');
    Route::match(array('GET', 'POST'), 'subscription/verify', [PaymentSubscriptionController::class, 'verify'])->name('payment.subscription.verify');
});

// admin
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'admin']], function () {

    Route::group(['prefix' => 'packages', 'as' => 'packages.'], function () {
        Route::get('/', [PackageController::class, 'index'])->name('index');
        Route::get('get-info', [PackageController::class, 'getInfo'])->name('get.info'); // ajax
        Route::post('store', [PackageController::class, 'store'])->name('store');
        Route::get('destroy/{id}', [PackageController::class, 'destroy'])->name('destroy');
        Route::get('owner', [PackageController::class, 'ownerPackage'])->name('owner');
        Route::post('assign', [PackageController::class, 'assignPackage'])->name('assign');
    });

    Route::group(['prefix' => 'message', 'as' => 'message.'], function () {
        Route::get('/', [ContactMessageController::class, 'index'])->name('index');
        Route::get('get-info', [ContactMessageController::class, 'getInfo'])->name('get.info'); // ajax
        Route::post('reply', [ContactMessageController::class, 'reply'])->name('reply');
    });

    Route::group(['prefix' => 'subscriptions', 'as' => 'subscriptions.'], function () {
        Route::get('orders', [SubscriptionController::class, 'orders'])->name('orders');
        Route::get('orders/get-info', [SubscriptionController::class, 'orderGetInfo'])->name('orders.get.info'); // ajax
        Route::post('orders/payment-status-change', [SubscriptionController::class, 'orderPaymentStatusChange'])->name('order.payment.status.change');
        Route::get('orders-payment-status', [SubscriptionController::class, 'ordersStatus'])->name('orders.payment.status'); // ajax
    });

    Route::group(['prefix' => 'setting', 'as' => 'setting.'], function () {

        Route::get('terms-conditions', [PolicyController::class, 'termsConditions'])->name('terms-conditions');
        Route::get('privacy-policy', [PolicyController::class, 'privacyPolicy'])->name('privacy-policy');
        Route::get('cookie-policy', [PolicyController::class, 'cookiePolicy'])->name('cookie-policy');

        Route::group(['prefix' => 'gateway', 'as' => 'gateway.'], function () {
            Route::get('/', [GatewayController::class, 'index'])->name('index');
            Route::post('store', [GatewayController::class, 'store'])->name('store');
            Route::get('get-info', [GatewayController::class, 'getInfo'])->name('get.info');
            Route::get('delete/{gateway}', [GatewayController::class, 'delete'])->name('delete');
        });
    });

    Route::group(['prefix' => 'home-setting', 'as' => 'home-setting.'], function () {
        Route::get('section', [HomeSettingController::class, 'section'])->name('section');
    });

    Route::group(['prefix' => 'feature', 'as' => 'feature.'], function () {
        Route::get('/', [FeatureController::class, 'index'])->name('index');
        Route::post('store', [FeatureController::class, 'store'])->name('store');
        Route::get('get-info', [FeatureController::class, 'getInfo'])->name('get.info');
        Route::delete('destroy/{id}', [FeatureController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => 'how-it-work', 'as' => 'how-it-work.'], function () {
        Route::get('/', [HowItWorkController::class, 'index'])->name('index');
        Route::post('store', [HowItWorkController::class, 'store'])->name('store');
        Route::get('get-info', [HowItWorkController::class, 'getInfo'])->name('get.info');
        Route::delete('destroy/{id}', [HowItWorkController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => 'core-page', 'as' => 'core-page.'], function () {
        Route::get('/', [CorePagesController::class, 'index'])->name('index');
        Route::post('store', [CorePagesController::class, 'store'])->name('store');
        Route::get('get-info', [CorePagesController::class, 'getInfo'])->name('get.info');
        Route::delete('destroy/{id}', [CorePagesController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => 'testimonials', 'as' => 'testimonials.'], function () {
        Route::get('/', [TestimonialController::class, 'index'])->name('index');
        Route::post('store', [TestimonialController::class, 'store'])->name('store');
        Route::get('get-info', [TestimonialController::class, 'getInfo'])->name('get.info');
        Route::delete('destroy/{id}', [TestimonialController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => 'faq', 'as' => 'faq.'], function () {
        Route::get('/', [FaqController::class, 'index'])->name('index');
        Route::post('store', [FaqController::class, 'store'])->name('store');
        Route::get('get-info', [FaqController::class, 'getInfo'])->name('get.info');
        Route::delete('destroy/{id}', [FaqController::class, 'destroy'])->name('destroy');
    });
});

// owner
Route::group(['prefix' => 'owner', 'as' => 'owner.', 'middleware' => ['auth', 'owner']], function () {
    Route::group(['prefix' => 'subscription', 'as' => 'subscription.'], function () {
        Route::get('/', [SaasSubscriptionController::class, 'index'])->name('index');
        Route::post('order', [SaasSubscriptionController::class, 'order'])->name('order');
        Route::get('get-plan', [SaasSubscriptionController::class, 'getPlan'])->name('get_plan');
        Route::get('get-currency-by-gateway', [SaasSubscriptionController::class, 'getCurrencyByGateway'])->name('get.currency');
        Route::post('cancel', [SaasSubscriptionController::class, 'cancel'])->name('cancel');
    });
});
