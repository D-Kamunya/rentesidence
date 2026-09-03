<?php

use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\DocumentController;
use App\Http\Controllers\Tenant\InformationController;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\ProductOrderController;
use App\Http\Controllers\Tenant\UtilityTokenController;
use App\Http\Controllers\Tenant\MaintenanceRequestController;
use App\Http\Controllers\Tenant\TicketController;
use App\Http\Controllers\Tenant\RentalScoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'tenant', 'as' => 'tenant.', 'middleware' => ['auth', 'tenant']], function () {
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('notification', [DashboardController::class, 'notification'])->name('notification');
    Route::get('notices', [DashboardController::class, 'notices'])->name('notices');

    // My Rental Score — the tenant-owned Global Tenant ID (transparency + activate + dispute).
    Route::group(['prefix' => 'rental-score', 'as' => 'rental-score.'], function () {
        Route::get('/', [RentalScoreController::class, 'index'])->name('index');
        Route::post('activate', [RentalScoreController::class, 'activate'])->name('activate');
        Route::post('dispute', [RentalScoreController::class, 'dispute'])->name('dispute');
        Route::post('dispute/reply', [RentalScoreController::class, 'disputeReply'])->name('dispute.reply');
    });

    Route::group(['prefix' => 'invoice', 'as' => 'invoice.'], function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('print/{id}', [InvoiceController::class, 'details'])->name('print');
        Route::get('pay/{id}', [InvoiceController::class, 'pay'])->name('pay');
        Route::get('receipt/{id}', [InvoiceController::class, 'receipt'])->name('receipt');
        Route::post('pay-upcoming', [InvoiceController::class, 'generateUpcoming'])->name('pay.upcoming');
        Route::get('get-currency-by-gateway', [InvoiceController::class, 'getCurrencyByGateway'])->name('get.currency');
    });

    // Tenant responds to a recorded deposit settlement (confirm receipt / dispute).
    Route::post('deposit-settlement/{id}/respond', [\App\Http\Controllers\Tenant\DepositSettlementController::class, 'respond'])->name('deposit-settlement.respond');

    // Notice to vacate (move-out lifecycle).
    Route::group(['prefix' => 'vacation-notice', 'as' => 'vacation-notice.'], function () {
        Route::post('/', [\App\Http\Controllers\Tenant\VacationNoticeController::class, 'store'])->name('store');
    });

    Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
        Route::get('/', [ProductOrderController::class, 'index'])->name('index');
        // Route::get('print/{id}', [InvoiceController::class, 'details'])->name('print');
        // Route::get('pay/{id}', [InvoiceController::class, 'pay'])->name('pay');
        // Route::get('get-currency-by-gateway', [InvoiceController::class, 'getCurrencyByGateway'])->name('get.currency');
    });

    Route::group(['prefix' => 'information', 'as' => 'information.'], function () {
        Route::get('/', [InformationController::class, 'index'])->name('index');
        Route::get('get-info', [InformationController::class, 'getInfo'])->name('get.info'); // ajax
    });

    Route::group(['prefix' => 'document', 'as' => 'document.'], function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::post('store', [DocumentController::class, 'store'])->name('store');
        Route::get('get-info', [DocumentController::class, 'getInfo'])->name('get.info'); // ajax
        Route::get('get-config-info', [DocumentController::class, 'getConfigInfo'])->name('get.config.info'); // ajax
        Route::delete('delete/{id}', [DocumentController::class, 'delete'])->name('delete');
    });

    Route::group(['prefix' => 'ticket', 'as' => 'ticket.'], function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('get-info', [TicketController::class, 'getInfo'])->name('get.info'); // ajax
        Route::get('details/{id}', [TicketController::class, 'details'])->name('details');
        Route::post('store', [TicketController::class, 'store'])->name('store');
        Route::post('reply', [TicketController::class, 'reply'])->name('reply');
        Route::get('status-change', [TicketController::class, 'statusChange'])->name('status.change');
        Route::delete('delete/{id}', [TicketController::class, 'delete'])->name('delete');
        Route::get('search', [TicketController::class, 'search'])->name('search'); // ajax
    });

    Route::group(['prefix' => 'maintenance-request', 'as' => 'maintenance-request.'], function () {
        Route::get('/', [MaintenanceRequestController::class, 'index'])->name('index');
        Route::post('store', [MaintenanceRequestController::class, 'store'])->name('store');
        Route::get('get-info', [MaintenanceRequestController::class, 'getInfo'])->name('get.info'); // ajax
    });

    Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
        Route::get('/', [ProductController::class, 'showProductsForTenant'])->name('index');
        Route::get('details/{id}', [ProductController::class, 'show'])->name('details');
        Route::get('pay', [ProductController::class, 'pay'])->name('pay');
        Route::get('orders/{id}/receipt', [ProductController::class, 'receipt'])->name('order.receipt');
    });
    
    Route::post('orders/{id}/cancel', [ProductOrderController::class, 'cancel'])->name('product_order.cancel');
    Route::post('orders/{id}/request-refund', [ProductOrderController::class, 'requestRefund'])->name('product_order.request-refund');
    Route::post('orders/{id}/confirm-receipt', [ProductOrderController::class, 'confirmReceipt'])->name('product_order.confirm-receipt');

    Route::group(['prefix' => 'utilities', 'as' => 'utilities.'], function () {
        Route::get('/', [UtilityTokenController::class, 'index'])->name('index');
        Route::post('purchase', [UtilityTokenController::class, 'purchase'])->name('purchase');
    });
});

Route::get('/pay/invoice/{token}', [InvoiceController::class, 'instantRentPayShow'])
    ->name('instant.invoice.pay');

// Throttled: unauthenticated STK-trigger endpoint — cap attempts per IP so a leaked
// pay link can't be used to spam STK prompts at an arbitrary phone number.
Route::post('/instant-invoice-pay/{token}', [PaymentController::class, 'instantCheckout'])
    ->middleware('throttle:6,1')
    ->name('instant.payment.checkout');

