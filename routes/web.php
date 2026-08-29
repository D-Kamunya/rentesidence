<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Listing\HouseHuntController;
use App\Http\Controllers\ProductPaymentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserEmailVerifyController;
use App\Http\Controllers\VersionUpdateController;
use App\Models\Language;
use App\Http\Controllers\BlogController;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register' => false]);

Route::get('/local/{ln}', function ($ln) {
    $language = Language::where('code', $ln)->first();
    if (!$language) {
        $language = Language::where('default', 1)->first();
        if ($language) {
            $ln = $language->code;
        }
    }

    session(['local' => $ln]);
    Carbon::setLocale($ln);
    App::setLocale(session()->get('local'));
    return redirect()->back();
})->name('local');

Route::group(['middleware' => ['version.update', 'addon.update', 'isFrontend']], function () {
    Route::get('/', [CommonController::class, 'index'])->name('frontend');
    Route::get('recurring-generate-invoice', [CommonController::class, 'generateInvoice'])->name('recurring.generate.invoice');
});

Route::group(['middleware' => ['auth', 'version.update']], function () {
    Route::get('/logout', [LoginController::class, 'logout']);
    Route::group(['middleware' => ['addon.update']], function () {
        Route::get('profile', [ProfileController::class, 'myProfile'])->name('profile');
        Route::post('profile', [ProfileController::class, 'profileUpdate'])->name('profile.update');
        Route::get('change-password', [ProfileController::class, 'changePassword'])->name('change-password');
        Route::post('change-password', [ProfileController::class, 'changePasswordUpdate'])->name('change-password.update');
        Route::post('delete-my-account', [ProfileController::class, 'deleteMyAccount'])->name('delete-my-account');

        Route::get('notification-status/{id}/{role}', [NotificationController::class, 'status'])->name('notification.status');
    });
});

Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
    Route::get('email/verified/{token}', [UserEmailVerifyController::class, 'emailVerified'])->name('email.verified');
    Route::get('email/verify/{token}', [UserEmailVerifyController::class, 'emailVerify'])->name('email.verify');
    Route::post('email/verify/resend/{token}', [UserEmailVerifyController::class, 'emailVerifyResend'])->name('email.verify.resend');
});

Route::group(['prefix' => 'payment'], function () {
    // Checkout is initiated only by an authenticated tenant (the invoice / marketplace
    // pay pages). Require auth so a guest can't POST here and spawn orphan orders / hit
    // null-user errors. (The guest instant-pay flow uses the separate, throttled
    // `instant.payment.checkout`.) The verify/redirect routes below stay public by
    // design — the guest instant-pay redirect lands on them, and money effects there are
    // gated on server-side STK confirmation, not on the request being authenticated.
    Route::post('/', [PaymentController::class, 'checkout'])->middleware('auth')->name('payment.checkout');
    Route::post('/products', [ProductPaymentController::class, 'checkout'])->middleware('auth')->name('payment.products.checkout');
    Route::match(array('GET', 'POST'), 'verify', [PaymentController::class, 'verify'])->name('payment.verify');
    Route::get('verify-redirect/{type?}', [PaymentController::class, 'verifyRedirect'])->name('payment.verify.redirect');
    Route::match(array('GET', 'POST'), 'products/verify', [ProductPaymentController::class, 'verify'])->name('payment.products.verify');
    Route::get('products/verify-redirect/{type?}', [PaymentController::class, 'verifyRedirect'])->name('payment.products.verify.redirect');
});

// Public Blog Routes
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    // Static routes MUST come before the /{slug} catch-all, or they get matched as a
    // post slug (this is why unsubscribe silently 404'd before).
    Route::get('/unsubscribe', [BlogController::class, 'unsubscribe'])->name('unsubscribe');
    Route::post('/subscribe', [BlogController::class, 'subscribe'])->name('subscribe');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
    Route::post('/{post}/comment', [BlogController::class, 'comment'])->name('comment');
    Route::post('/{post}/like', [BlogController::class, 'like'])->name('like');
    Route::post('/{post}/share', [BlogController::class, 'share'])->name('share');
});

// Public certificate verification — anyone holding a signed agreement certificate can
// confirm its authenticity (gated by the unguessable code, so agreements can't be enumerated).
Route::get('/agreement/verify/{code?}', [\App\Http\Controllers\Agreement\VerificationController::class, 'show'])->name('agreement.verify');
Route::post('/agreement/verify/{code}/document', [\App\Http\Controllers\Agreement\VerificationController::class, 'check'])->name('agreement.verify.document');
Route::post('/agreement/verify-by-document', [\App\Http\Controllers\Agreement\VerificationController::class, 'checkByDocument'])->name('agreement.verify.by-document');

Route::get('/get-filters', [HouseHuntController::class, 'getFiltersByType'])->name('get.filters');
Route::get('/get-cities', [HouseHuntController::class, 'getCitiesByState'])->name('get.cities');
Route::get('/house-hunt', [HouseHuntController::class, 'index'])->name('house.hunt');
Route::get('/house-hunt/view/{propertyId}', [HouseHuntController::class, 'viewProperty'])->name('house-hunt.view');
// Route::get('/owner/tenants/applications', function () {return view('owner.tenants.applications');})->name('owner.tenants.applications');
Route::get('version-update', [VersionUpdateController::class, 'versionUpdate'])->name('version-update');
Route::post('process-update', [VersionUpdateController::class, 'processUpdate'])->name('process-update');
Route::get('version-check', [VersionUpdateController::class, 'versionCheck'])->name('versionCheck');
