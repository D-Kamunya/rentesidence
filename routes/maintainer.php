<?php


use App\Http\Controllers\Maintainer\DashboardController;
use App\Http\Controllers\Maintainer\InformationController;
use App\Http\Controllers\Maintainer\DispatchController;
use App\Http\Controllers\Maintainer\MaintenanceRequestController;
use App\Http\Controllers\Maintainer\RentController;
use App\Http\Controllers\Maintainer\TicketController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'maintainer', 'as' => 'maintainer.', 'middleware' => ['auth', 'maintainer']], function () {
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('notification', [DashboardController::class, 'notification'])->name('notification');

    // Read-only rent visibility (caretaker can see paid/arrears on their patch; never change status).
    Route::get('rent', [RentController::class, 'index'])->name('rent.index');
    // Owner-gated cash confirmation (owners.caretaker_can_confirm_rent). List a tenant's unpaid
    // invoices, then confirm one as paid-in-cash (attributed to the caretaker, owner notified).
    Route::get('rent/invoices', [RentController::class, 'invoices'])->name('rent.invoices');
    Route::post('rent/confirm', [RentController::class, 'confirm'])->name('rent.confirm');

    // Dispatch queue — fulfil paid marketplace orders for tenants on the caretaker's properties.
    Route::group(['prefix' => 'dispatch', 'as' => 'dispatch.'], function () {
        Route::get('/', [DispatchController::class, 'index'])->name('index');
        Route::post('{id}/dispatch', [DispatchController::class, 'dispatchOrder'])->name('dispatch');
        Route::post('{id}/deliver', [DispatchController::class, 'deliver'])->name('deliver');
    });

    Route::group(['prefix' => 'information', 'as' => 'information.'], function () {
        Route::get('/', [InformationController::class, 'index'])->name('index');
        Route::get('get-info', [InformationController::class, 'getInfo'])->name('get.info'); // ajax
    });

    Route::group(['prefix' => 'maintenance-request', 'as' => 'maintenance-request.'], function () {
        Route::get('/', [MaintenanceRequestController::class, 'index'])->name('index');
        Route::get('get-info', [MaintenanceRequestController::class, 'getInfo'])->name('get.info'); // ajax
        Route::post('status-change', [MaintenanceRequestController::class, 'statusChange'])->name('status.change');
    });

    Route::group(['prefix' => 'ticket', 'as' => 'ticket.'], function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('details/{id}', [TicketController::class, 'details'])->name('details');
        Route::post('reply', [TicketController::class, 'reply'])->name('reply');
        Route::get('status-change', [TicketController::class, 'statusChange'])->name('status.change');
    });
});
