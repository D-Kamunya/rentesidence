<?php

use App\Http\Controllers\FinancePartner\KnowledgeBaseController;
use App\Http\Controllers\FinancePartner\PortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Finance Partner Portal (role 6)
|--------------------------------------------------------------------------
| Where Centresidence finance partners manage the products they offer and
| review owner applications.
*/

Route::group(['prefix' => 'finance-partner', 'as' => 'finance-partner.', 'middleware' => ['auth', 'finance_partner']], function () {
    Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');

    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [PortalController::class, 'products'])->name('index');
        Route::get('create', [PortalController::class, 'productCreate'])->name('create');
        Route::post('/', [PortalController::class, 'productStore'])->name('store');
        Route::get('{id}/edit', [PortalController::class, 'productEdit'])->name('edit');
        Route::put('{id}', [PortalController::class, 'productUpdate'])->name('update');
    });

    Route::prefix('applications')->name('applications.')->group(function () {
        Route::get('/', [PortalController::class, 'applications'])->name('index');
        Route::get('{id}', [PortalController::class, 'applicationShow'])->name('show');
        Route::post('{id}/approve', [PortalController::class, 'approve'])->name('approve');
        Route::post('{id}/reject', [PortalController::class, 'reject'])->name('reject');
    });

    Route::get('facilities', [PortalController::class, 'facilities'])->name('facilities');
    Route::get('facilities/{id}/overview', [PortalController::class, 'facilityOverview'])->name('facilities.overview');
    Route::post('facilities/{id}/record-disbursement', [PortalController::class, 'recordDisbursement'])->name('facilities.record-disbursement');
    Route::post('facilities/{id}/confirm-settlement', [PortalController::class, 'confirmSettlement'])->name('facilities.confirm-settlement');
    Route::get('remittances', [PortalController::class, 'remittances'])->name('remittances');
    Route::post('remittances/{id}/confirm', [PortalController::class, 'confirmRemittance'])->name('remittances.confirm');

    Route::get('payout-account', [PortalController::class, 'payoutAccount'])->name('payout-account');
    Route::post('payout-account', [PortalController::class, 'payoutAccountSave'])->name('payout-account.save');

    Route::get('notifications', [PortalController::class, 'notification'])->name('notification');
    Route::get('profile', [PortalController::class, 'profile'])->name('profile');
    Route::post('profile', [PortalController::class, 'profileUpdate'])->name('profile.update');
    Route::post('profile/password', [PortalController::class, 'passwordUpdate'])->name('profile.password');

    // Learn — module education (financier lens)
    Route::prefix('learn')->name('learn.')->group(function () {
        Route::get('modules', [PortalController::class, 'learnModules'])->name('modules');
        Route::get('modules/{id}', [PortalController::class, 'learnModule'])->name('module');
    });

    // Knowledge base — admin-authored partner guides
    Route::prefix('knowledge-base')->name('kb.')->group(function () {
        Route::get('/', [KnowledgeBaseController::class, 'index'])->name('index');
        Route::get('/search', [KnowledgeBaseController::class, 'search'])->name('search');
        Route::get('/category/{category:slug}', [KnowledgeBaseController::class, 'category'])->name('category');
        Route::get('/article/{article:slug}', [KnowledgeBaseController::class, 'article'])->name('article');
        Route::get('/document/{article}/download', [KnowledgeBaseController::class, 'downloadDocument'])->name('document.download');
    });
});
