<?php

use App\Http\Controllers\Affiliates\DashboardController;
use App\Http\Controllers\Affiliates\MarketingToolController;
use App\Http\Controllers\Affiliates\AcademyController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => 'affiliate',
    'as' => 'affiliate.',
    'middleware' => ['auth', 'affiliate'] // auth for all affiliates
], function () {

    // -------------------------------
    // Academy Routes (accessible to all affiliates)
    // -------------------------------
    Route::prefix('academy')->group(function () {
        Route::get('/', [AcademyController::class, 'index'])->name('academy.index');
        Route::get('/{module}', [AcademyController::class, 'show'])->name('academy.show');
        Route::post('/{module}/submit', [AcademyController::class, 'submit'])->name('academy.submit');
    });

    // -------------------------------
    // Locked Affiliate Routes (requires academy completion)
    // -------------------------------
    Route::middleware('academy.completed')->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

        // Marketing tool
        Route::get('marketing-tool', [MarketingToolController::class, 'index'])->name('marketing-tools');

        // Withdrawals
        Route::post('withdraw', function (Request $request) {
            // Handle the withdrawal logic here
            return back()->with('success', 'Withdrawal request submitted!');
        })->name('withdraw');
        // Certificate
        Route::get('affiliate/academy/certificate', [AcademyController::class, 'certificate'])->name('academy.certificate');
    });

});

// -------------------------------
// Admin Override for Failing Affiliates
// -------------------------------
Route::group([
    'prefix' => 'affiliate/admin',
    'as' => 'affiliate.admin.',
    'middleware' => ['auth', 'admin']
], function () {
    Route::post('/reset-attempts/{progress}', function (\App\Models\AffiliateAcademyProgress $progress) {

        $progress->attempts = 0;
        $progress->needs_review = false;
        $progress->completed_at = null;
        $progress->score = null;
        $progress->save();

        return back()->with('success', 'Affiliate can now retry the module.');
    })->name('reset-attempts');
});