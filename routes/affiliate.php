<?php

use App\Http\Controllers\Affiliates\DashboardController;
use App\Http\Controllers\Affiliates\MarketingToolController;
use App\Http\Controllers\Affiliates\AcademyController;
use App\Http\Controllers\Affiliates\ActionExecutionController;
use App\Http\Controllers\Affiliates\LeadSuggestionController;
use App\Http\Controllers\Affiliates\AffiliatesMarketplaceController;
use App\Http\Controllers\Affiliates\LeadController;
use App\Http\Controllers\Affiliates\AffiliateMarketingMaterialController;
use App\Http\Controllers\Affiliates\CommissionsController;
use App\Http\Controllers\Affiliates\AffiliateWithdrawalController;
use App\Http\Controllers\Affiliates\ReferralsController;
use App\Http\Controllers\Affiliates\AffiliateKnowledgeBaseController;
use App\Http\Controllers\Affiliates\LeaderboardController;
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
            Route::get('/commissions',        [CommissionsController::class, 'index'])->name('commissions.index');
            Route::get('/commissions/detail', [CommissionsController::class, 'detail'])->name('commissions.detail');
            Route::post('/withdraw',          [AffiliateWithdrawalController::class, 'withdraw'])->name('withdraw');
            // Certificate
            Route::get('affiliate/academy/certificate', [AcademyController::class, 'certificate'])->name('academy.certificate');
            // Affiliates Leads CRUD functions
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
            // Affiliate prompt actions
            Route::get('/action/whatsapp/{lead}/{template}', [ActionExecutionController::class, 'whatsapp'])->name('action.whatsapp');
            Route::get('/action/email/{lead}/{template}', [ActionExecutionController::class, 'email'])->name('action.email');
            Route::get('/action/call/{lead}', [ActionExecutionController::class, 'call'])->name('action.call');
            Route::get('/action/call/{lead}/{template}', [ActionExecutionController::class, 'callView'])->name('action.call.view');
            // Affiliate suggestions
            Route::get('/suggestions/lead/{lead}', [LeadSuggestionController::class, 'leadSuggestions'])->name('suggestions.lead');
            Route::get('/my-suggestions', [LeadSuggestionController::class, 'mySuggestions'])->name('suggestions.mine');
            Route::post('/suggestions/{id}/complete', [LeadSuggestionController::class, 'complete'])->name('suggestions.complete');
            Route::post('/suggestions/{id}/dismiss', [LeadSuggestionController::class, 'dismiss'])->name('suggestions.dismiss');
            // Affiliate Leads Marketplace
            Route::get('/marketplace', [AffiliatesMarketplaceController::class, 'index'])->name('marketplace.index');
            Route::post('/marketplace/{lead}/claim', [AffiliatesMarketplaceController::class, 'claim'])->name('marketplace.claim');
            // Affiliate Marketing materials
            Route::get('/materials', [AffiliateMarketingMaterialController::class, 'index'])->name('materials.index');
            // Refferals
            Route::get('/referrals', [ReferralsController::class, 'index'])->name('referrals.index');
            Route::get('/referrals/{owner}', [ReferralsController::class, 'show'])->name('referrals.show');
            Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');

            // knowledge base
            Route::prefix('knowledge-base')->name('kb.')->group(function () {
                Route::get('/', [AffiliateKnowledgeBaseController::class, 'index'])->name('index');
                Route::get('/search', [AffiliateKnowledgeBaseController::class, 'search'])->name('search');
                Route::get('/category/{category:slug}', [AffiliateKnowledgeBaseController::class, 'category'])->name('category');
                Route::get('/article/{article:slug}', [AffiliateKnowledgeBaseController::class, 'article'])->name('article');
                Route::get('/document/{article}/download', [AffiliateKnowledgeBaseController::class, 'downloadDocument'])->name('document.download');
            });
        });
    });