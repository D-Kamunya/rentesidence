<?php

use App\Http\Controllers\Affiliates\DashboardController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'affiliate', 'as' => 'affiliate.'], function () {
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::post('withdraw', function (Request $request) {
    // Handle the withdrawal logic here
        return back()->with('success', 'Withdrawal request submitted!');
    })->name('withdraw');

});
