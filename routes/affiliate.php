<?php

use App\Http\Controllers\Affiliates\DashboardController;
use App\Http\Controllers\Affiliates\MarketingToolController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'affiliate', 'as' => 'affiliate.', 'middleware' => ['auth', 'affiliate']], function () {
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('marketing-tool', [MarketingToolController::class, 'index'])->name('marketing-tools');
    Route::post('withdraw', function (Request $request) {
    // Handle the withdrawal logic here
        return back()->with('success', 'Withdrawal request submitted!');
    })->name('withdraw');

});
