<?php

use App\Http\Controllers\SmsMail\EmailTemplateController;
use App\Http\Controllers\SmsMail\MailController;
use App\Http\Controllers\SmsMail\SmsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'sms-mail', 'as' => 'sms-mail.', 'middleware' => ['auth']], function () {
    Route::get('sms', [SmsController::class, 'index'])->name('sms');
    Route::post('sms-send', [SmsController::class, 'send'])->name('sms.send');
    Route::post('sms-test-send', [SmsController::class, 'testSend'])->name('sms.test.send');

    Route::get('mail', [MailController::class, 'index'])->name('mail');
    Route::post('mail-send', [MailController::class, 'send'])->name('mail.send');

    Route::group(['prefix' => 'template', 'as' => 'template.'], function () {
        Route::get('/', [EmailTemplateController::class, 'index'])->name('index');
        Route::post('store', [EmailTemplateController::class, 'store'])->name('store');
        Route::get('details', [EmailTemplateController::class, 'details'])->name('details');
        Route::post('email-temp-update/{id}', [EmailTemplateController::class, 'emailTempUpdate'])->name('email-temp-update');
    });
});
