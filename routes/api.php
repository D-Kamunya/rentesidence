<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentSubscriptionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MpesaController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('owner-register', [AuthController::class, 'ownerRegister']);
Route::post('otp-verify', [AuthController::class, 'otpVerify']);
Route::post('otp-re-send', [AuthController::class, 'otpReSend']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);

// setting
Route::get('system-currency', [SettingController::class, 'systemCurrency']);
Route::get('system-setting', [SettingController::class, 'systemSetting']);
Route::get('languages', [SettingController::class, 'getLanguage']);
Route::get('language-data/{code}', [SettingController::class, 'getLanguageJson']);


Route::group(['middleware' => ['auth:api']], function () {
    // profile
    Route::get('profile-details', [ProfileController::class, 'profileDetails']);
    Route::post('profile-update', [ProfileController::class, 'profileUpdate']);
    Route::post('change-password', [ProfileController::class, 'changePasswordUpdate']);
    Route::post('delete-account', [ProfileController::class, 'deleteAccount']);

    // notification
    Route::get('notification-status/{id}', [NotificationController::class, 'status']);
});

// payment route start
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('payment-subscription', [PaymentSubscriptionController::class, 'checkout']);
    Route::post('payment', [PaymentController::class, 'checkout']);
});

Route::post('payment/confirm', [MpesaController::class, 'MpesaPaymentConfirm'])->name('mpesa.payment.confirm');
Route::post('v1/b2c/result',   [MpesaController::class, 'B2CResult'])->name('mpesa.b2c.result');

// Centresidence owner down-payment (partial financing) STK callback.
Route::post('centresidence/down-payment/{facility}/callback', \App\Http\Controllers\Centresidence\DownPaymentCallbackController::class)
    ->name('centresidence.down-payment.callback');

// Centresidence partner remittance (M-Pesa B2B) result callback — confirms/fails a payout batch.
Route::post('centresidence/remittance/{batch}/callback', \App\Http\Controllers\Centresidence\PartnerRemittanceCallbackController::class)
    ->name('centresidence.remittance.callback');

// Centresidence owner module-infrastructure bill (M-Pesa STK) callback — marks the bill paid.
Route::post('centresidence/infra-bill/{owner}/callback', \App\Http\Controllers\Centresidence\InfraBillCallbackController::class)
    ->name('centresidence.infra-bill.callback');

// Centresidence tenant utility-token purchase (M-Pesa STK) callback — credits wallet units.
Route::post('centresidence/token/{propertyModule}/{tenant}/callback', \App\Http\Controllers\Centresidence\TokenPurchaseCallbackController::class)
    ->name('centresidence.token.callback');

// Centresidence owner early-settlement payoff (M-Pesa STK) callback — completes the facility.
Route::post('centresidence/settle/{facility}/callback', \App\Http\Controllers\Centresidence\SettleEarlyCallbackController::class)
    ->name('centresidence.settle.callback');

// Centresidence LoRaWAN inbound: ChirpStack HTTP integration posts device events
// here (join → activate device, up → consumption drawdown, txack → downlink ack).
// Fail-closed on the shared webhook secret (see ChirpStackUplinkController).
Route::post('centresidence/chirpstack/uplink', \App\Http\Controllers\Centresidence\ChirpStackUplinkController::class)
    ->name('centresidence.chirpstack.uplink');


Route::match(array('GET', 'POST'), 'payment-subscription/verify', [PaymentSubscriptionController::class, 'verify']);
Route::match(array('GET', 'POST'), 'payment-verify', [PaymentController::class, 'verify']);
// payment route end
