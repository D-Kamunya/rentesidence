<?php

use App\Http\Controllers\Agreement\AgreementController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'owner', 'as' => 'owner.', 'middleware' => ['auth', 'owner']], function () {
    Route::group(['prefix' => 'agreement', 'as' => 'agreement.'], function () {
        Route::get('/', [AgreementController::class, 'index'])->name('index');
        Route::post('send', [AgreementController::class, 'send'])->name('send');
        Route::get('download/{envelope_id}', [AgreementController::class, 'download'])->name('download');
    });
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'admin']], function () {
    Route::group(['prefix' => 'agreement', 'as' => 'agreement.'], function () {
        Route::post('test-send', [AgreementController::class, 'testSend'])->name('test.send');
        Route::get('callback', [AgreementController::class, 'callback'])->name('callback');
        Route::get('test', [AgreementController::class, 'test'])->name('test');
    });
});

Route::group(['prefix' => 'tenant', 'as' => 'tenant.', 'middleware' => ['auth', 'tenant']], function () {
    Route::group(['prefix' => 'agreement', 'as' => 'agreement.'], function () {
        Route::get('/', [AgreementController::class, 'tenantAgreement'])->name('index');
        Route::get('download/{envelope_id}', [AgreementController::class, 'tenantDownload'])->name('download');
    });
});
