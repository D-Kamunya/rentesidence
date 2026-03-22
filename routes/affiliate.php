<?php

use App\Http\Controllers\Affiliates\DashboardController;
use App\Http\Controllers\Affiliates\MarketingToolController;
use App\Http\Controllers\Affiliates\AcademyController;
use App\Http\Controllers\Affiliates\LeadController;
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
                return back()->with('success', 'Withdrawal request submitted!');})->name('withdraw');
            // Certificate
            Route::get('affiliate/academy/certificate', [AcademyController::class, 'certificate'])->name('academy.certificate');
            // Leads CRUD functions
            Route::get('/affiliate/leads', [LeadController::class,'index'])->name('leads');
            Route::get('/affiliate/leads/create', [LeadController::class,'create'])->name('leads.create');
            Route::post('/affiliate/leads/store', [LeadController::class,'store'])->name('leads.store');
            Route::get('/affiliate/leads/{lead}/edit', [LeadController::class,'edit'])->name('leads.edit');
            Route::post('/affiliate/leads/{lead}/update', [LeadController::class,'update'])->name('leads.update');
            Route::get('/affiliate/leads/{lead}', [LeadController::class, 'show']) ->name('leads.show');
            Route::post('/affiliate/leads/{lead}/renew', [LeadController::class,'renew'])->name('leads.renew');
            // Affiliate lead status changes
            Route::post('{lead}/note', [LeadController::class,'addNote'])->name('leads.addNote');
            Route::post('{lead}/temperature', [LeadController::class,'updateTemperature'])->name('leads.temperature');
            Route::post('{lead}/schedule-demo', [LeadController::class,'scheduleDemo'])->name('leads.scheduleDemo');
            Route::post('{lead}/demo-completed', [LeadController::class,'demoCompleted'])->name('leads.demoCompleted');
            Route::post('{lead}/convert', [LeadController::class,'requestTrial'])->name('leads.requesttrial');
            Route::post('{lead}/reject', [LeadController::class,'reject'])->name('leads.reject');
            Route::post('{lead}/lost', [LeadController::class,'lost'])->name('leads.lost');

            });
    });