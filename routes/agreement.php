<?php

use App\Http\Controllers\Agreement\AgreementController;
use App\Http\Controllers\Owner\CreditTopUpController;
use Illuminate\Support\Facades\Route;

/*
 | Internal e-signature agreements (replaces DocuSign). Owner sends from a reusable
 | template; tenant reviews + signs in-portal behind an SMS OTP.
 */

Route::group(['prefix' => 'owner', 'as' => 'owner.', 'middleware' => ['auth', 'owner']], function () {
    Route::group(['prefix' => 'agreement', 'as' => 'agreement.'], function () {
        Route::get('/', [AgreementController::class, 'index'])->name('index');
        Route::post('send', [AgreementController::class, 'send'])->name('send');
        Route::get('show/{id}', [AgreementController::class, 'show'])->name('show');
        Route::get('download/{id}', [AgreementController::class, 'download'])->name('download');
        Route::get('document/{id}', [AgreementController::class, 'document'])->name('document');

        Route::get('templates', [AgreementController::class, 'templates'])->name('templates');
        Route::post('templates/{id}', [AgreementController::class, 'templateUpdate'])->name('template.update');
        Route::get('templates/{id}/document', [AgreementController::class, 'templateDocument'])->name('template.document');

        // Agreement credit top-up (STK) — mirrors the hardened SMS-credits flow.
        Route::prefix('credits')->name('credits.')->group(function () {
            Route::post('checkout', [CreditTopUpController::class, 'checkout'])->defaults('bucket', 'agreement')->name('checkout');
            Route::match(['GET', 'POST'], 'verify', [CreditTopUpController::class, 'verify'])->defaults('bucket', 'agreement')->name('verify');
        });
    });
});

Route::group(['prefix' => 'tenant', 'as' => 'tenant.', 'middleware' => ['auth', 'tenant']], function () {
    Route::group(['prefix' => 'agreement', 'as' => 'agreement.'], function () {
        Route::get('/', [AgreementController::class, 'tenantAgreement'])->name('index');
        Route::get('show/{id}', [AgreementController::class, 'tenantShow'])->name('show');
        Route::post('otp/{id}', [AgreementController::class, 'requestOtp'])->name('otp');
        Route::post('sign/{id}', [AgreementController::class, 'sign'])->name('sign');
        Route::post('decline/{id}', [AgreementController::class, 'decline'])->name('decline');
        Route::get('download/{id}', [AgreementController::class, 'tenantDownload'])->name('download');
        Route::get('document/{id}', [AgreementController::class, 'tenantDocument'])->name('document');
    });
});
