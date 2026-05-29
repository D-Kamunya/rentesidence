<?php

use App\Http\Controllers\AddonUpdateController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\MailController;
use App\Http\Controllers\Admin\OwnerController;
use App\Http\Controllers\Admin\DemoPrepController;
use App\Http\Controllers\Admin\AffiliateController;
use App\Http\Controllers\Admin\AcademyAdminController;
use App\Http\Controllers\Admin\AffiliateLeadsController;
use App\Http\Controllers\Admin\MarketingMaterialController;
use App\Http\Controllers\Admin\ActionTemplateController;
use App\Http\Controllers\Admin\AdminLeadSuggestionController;
use App\Http\Controllers\Admin\AdminMarketplaceController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\AdminKnowledgeBaseController;
use App\Http\Controllers\Owner\OwnerWalletController;
use App\Http\Controllers\Owner\SmsCreditsController;
use App\Http\Controllers\Owner\SmsCreditsPaymentController;
use App\Http\Controllers\Admin\SmsCreditsAdminController;
use App\Http\Controllers\Affiliates\AffiliateWithdrawalController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\VersionUpdateController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'admin']], function () {
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('notification', [DashboardController::class, 'notification'])->name('notification');
    // Academy landing (modules index)
    Route::get('academy', [AcademyAdminController::class, 'index'])->name('academy.index');
    // Modules CRUD
    Route::get('academy/create', [AcademyAdminController::class, 'create'])->name('academy.create');
    Route::post('academy', [AcademyAdminController::class, 'store'])->name('academy.store');
    Route::get('academy/{academy}/edit', [AcademyAdminController::class, 'edit'])->name('academy.edit');
    Route::put('academy/{academy}', [AcademyAdminController::class, 'update'])->name('academy.update');
    Route::delete('academy/{academy}', [AcademyAdminController::class, 'destroy'])->name('academy.destroy');
    // Questions
    Route::get('academy/{module}/questions', [AcademyAdminController::class, 'questions'])->name('academy.questions');
    Route::get('academy/{module}/questions/create', [AcademyAdminController::class, 'createQuestion'])->name('academy.questions.create');
    Route::post('academy/{module}/questions', [AcademyAdminController::class, 'storeQuestion'])->name('academy.questions.store');
    // Affiliate performance
    Route::get('/affiliates/performance', [AcademyAdminController::class, 'affiliatesPerformance'])->name('affiliates.performance');
    // Reset failed module
    Route::post('/affiliate/admin/reset-module/{affiliate}/{module}', [AcademyAdminController::class, 'resetAffiliateModule'])->name('reset-module');
    // Leads overview (index page)
    Route::get('/leads', [AffiliateLeadsController::class, 'index']) ->name('leads.index');
    // Single lead view (details page)
    Route::get('/leads/{lead}', [AffiliateLeadsController::class, 'show'])->name('leads.show');
    // Approve conversion → starts trial
    Route::post('/leads/{lead}/approve', [AffiliateLeadsController::class, 'approveTrial']) ->name('leads.approve');
    // Reject conversion
    Route::post('/leads/{lead}/reject', [AffiliateLeadsController::class, 'rejectTrial']) ->name('leads.reject');
    // Marketing Materials CRUD
    Route::get('/materials', [MarketingMaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/create', [MarketingMaterialController::class, 'create'])->name('materials.create');
    Route::post('/materials', [MarketingMaterialController::class, 'store'])->name('materials.store');
    Route::get('/materials/{id}/edit', [MarketingMaterialController::class, 'edit'])->name('materials.edit');
    Route::put('/materials/{id}', [MarketingMaterialController::class, 'update'])->name('materials.update');
    Route::delete('/materials/{id}', [MarketingMaterialController::class, 'destroy'])->name('materials.destroy');
    // Marketing Templates CRUD
    Route::get('/templates', [ActionTemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/create', [ActionTemplateController::class, 'create'])->name('templates.create');
    Route::post('/templates', [ActionTemplateController::class, 'store'])->name('templates.store');
    Route::get('/templates/{id}/edit', [ActionTemplateController::class, 'edit'])->name('templates.edit');
    Route::put('/templates/{id}', [ActionTemplateController::class, 'update'])->name('templates.update');
    Route::delete('/templates/{id}', [ActionTemplateController::class, 'destroy'])->name('templates.destroy');
    // Demo Prep Settings
    Route::get('/demo-prep', [DemoPrepController::class, 'index'])->name('demo_prep.index');
    Route::post('/demo-prep/settings', [DemoPrepController::class, 'updateSettings'])->name('demo_prep.settings.update');
    Route::post('/demo-prep/sections', [DemoPrepController::class, 'store'])->name('demo_prep.sections.store');
    Route::get('/demo-prep/sections/{id}/edit', [DemoPrepController::class, 'edit'])->name('demo_prep.sections.edit');
    Route::put('/demo-prep/sections/{id}', [DemoPrepController::class, 'update'])->name('demo_prep.sections.update');
    Route::delete('/demo-prep/sections/{id}', [DemoPrepController::class, 'destroy'])->name('demo_prep.sections.destroy');
    // Suggestions CRUD
    Route::get('/suggestions', [AdminLeadSuggestionController::class, 'index'])->name('suggestions.index');
    Route::get('/suggestions/lead/{lead}', [AdminLeadSuggestionController::class, 'lead'])->name('suggestions.lead');
    Route::post('/suggestions/generate', [AdminLeadSuggestionController::class, 'generate'])->name('suggestions.generate');
    Route::delete('/suggestions/{id}', [AdminLeadSuggestionController::class, 'destroy'])->name('suggestions.destroy');
    // Marketplace CRUD
    Route::get('/marketplace', [AdminMarketplaceController::class, 'index'])->name('marketplace.index');
    Route::get('/marketplace/create', [AdminMarketplaceController::class, 'create'])->name('marketplace.create');
    Route::post('/marketplace', [AdminMarketplaceController::class, 'store'])->name('marketplace.store');
    Route::get('/marketplace/{lead}', [AdminMarketplaceController::class, 'show'])->name('marketplace.show');
    Route::delete('/marketplace/{lead}', [AdminMarketplaceController::class, 'destroy'])->name('marketplace.destroy');
    // Activate/deactivate owners
    Route::post('/owner/activate/{id}', [OwnerController::class, 'activate'])->name('owner.activate');
    Route::post('/owner/deactivate/{id}', [OwnerController::class, 'deactivate'])->name('owner.deactivate');
    // Wallet actions
    Route::get('/wallet/commissions',          [OwnerWalletController::class, 'adminDashboard'])->name('wallet.commissions');
    Route::get('/wallet/owner/{wallet}',       [OwnerWalletController::class, 'adminOwnerWallet'])->name('wallet.owner');
    Route::post('/wallet/withdrawal/{withdrawal}/approve', [OwnerWalletController::class, 'approveWithdrawal'])->name('wallet.withdrawal.approve');
    Route::post('/wallet/withdrawal/{withdrawal}/reject',  [OwnerWalletController::class, 'rejectWithdrawal'])->name('wallet.withdrawal.reject');
    // Affiliate Withdrawals
    Route::get('/affiliate/withdrawals',                          [AffiliateWithdrawalController::class, 'adminIndex'])->name('affiliate.withdrawals');
    Route::post('/affiliate/withdrawal/{withdrawal}/approve',     [AffiliateWithdrawalController::class, 'approve'])->name('affiliate.withdrawal.approve');
    Route::post('/affiliate/withdrawal/{withdrawal}/reject',      [AffiliateWithdrawalController::class, 'reject'])->name('affiliate.withdrawal.reject');
    Route::get('/affiliate/{affiliate}/earnings', [AffiliateWithdrawalController::class, 'affiliateEarnings'])->name('affiliate.earnings');

    Route::prefix('knowledge-base')->name('kb.')->group(function () {
        // Categories
        Route::get('/categories', [AdminKnowledgeBaseController::class, 'categories'])->name('categories');
        Route::post('/categories', [AdminKnowledgeBaseController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [AdminKnowledgeBaseController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminKnowledgeBaseController::class, 'destroyCategory'])->name('categories.destroy');
        
        // Articles
        Route::get('/articles', [AdminKnowledgeBaseController::class, 'articles'])->name('articles');
        Route::get('/articles/create', [AdminKnowledgeBaseController::class, 'createArticle'])->name('articles.create');
        Route::post('/articles', [AdminKnowledgeBaseController::class, 'storeArticle'])->name('articles.store');
        Route::get('/articles/{article}/edit', [AdminKnowledgeBaseController::class, 'editArticle'])->name('articles.edit');
        Route::put('/articles/{article}', [AdminKnowledgeBaseController::class, 'updateArticle'])->name('articles.update');
        Route::delete('/articles/{article}', [AdminKnowledgeBaseController::class, 'destroyArticle'])->name('articles.destroy');
    });


    Route::prefix('sms-credits')->name('sms.credits.')->group(function () {
        Route::get('/',        [SmsCreditsAdminController::class, 'index'])->name('index');
        Route::put('/settings',[SmsCreditsAdminController::class, 'updateSettings'])->name('settings');
        Route::post('/topup',  [SmsCreditsAdminController::class, 'manualTopup'])->name('topup');
    });

    Route::group(['prefix' => 'owner', 'as' => 'owner.'], function () {
        Route::get('/', [OwnerController::class, 'index'])->name('index');
         // register owner
        Route::get('register', [OwnerController::class, 'owner_register_form'])->name('register.form');
        Route::post('register', [OwnerController::class, 'owner_register_store'])->name('register.store');

    });

    Route::group(['prefix' => 'affiliates', 'as' => 'affiliates.'], function () {
        Route::get('/', [AffiliateController::class, 'index'])->name('index');
         // register affiliate
        Route::get('register', [AffiliateController::class, 'affiliate_register_form'])->name('register.form');
        Route::post('register', [AffiliateController::class, 'affiliate_register_store'])->name('register.store');

    });

    Route::group(['prefix' => 'language', 'as' => 'language.'], function () {
        Route::get('/', [LanguageController::class, 'index'])->name('index');
        Route::post('store', [LanguageController::class, 'store'])->name('store')->middleware('isDemo');
        Route::post('update/{id}', [LanguageController::class, 'update'])->name('update')->middleware('isDemo');
        Route::delete('delete/{id}', [LanguageController::class, 'delete'])->name('delete');

        Route::get('translate/{id}/{iso_code?}', [LanguageController::class, 'translateLanguage'])->name('translate');
        Route::get('update-translate/{id}', [LanguageController::class, 'updateTranslate'])->name('update.translate');
        Route::post('import', [LanguageController::class, 'import'])->name('import');
    });

    Route::group(['prefix' => 'setting', 'as' => 'setting.'], function () {
        Route::get('general-setting', [SettingController::class, 'generalSetting'])->name('general-setting');
        Route::post('general-settings-update', [SettingController::class, 'generalSettingUpdate'])->name('general-setting.update');
        Route::get('color-setting', [SettingController::class, 'colorSetting'])->name('color-setting');
        Route::get('smtp-setting', [SettingController::class, 'smtpSetting'])->name('smtp.setting');
        Route::get('recaptcha-setting', [SettingController::class, 'recaptchaSetting'])->name('recaptcha.setting');
        Route::get('map-box-setting', [SettingController::class, 'mapBoxSetting'])->name('map-box.setting')->middleware('isDemo');
        Route::post('general-settings-env-update', [SettingController::class, 'generalSettingEnvUpdate'])->name('general-setting-env.update');
        Route::get('sms-setting', [SettingController::class, 'smsSetting'])->name('sms.setting');
        Route::get('tenancy-setting', [SettingController::class, 'tenancySetting'])->name('tenancy.setting');
        Route::get('frontend-setting', [SettingController::class, 'frontendSetting'])->name('frontend.setting');
        Route::get('listing-setting', [SettingController::class, 'listingSetting'])->name('listing.setting');
        Route::get('marketplaceaccounts-setting', [SettingController::class, 'marketplaceAccounts'])->name('marketplaceaccounts.setting');
        Route::post('marketplaceaccounts-setting', [SettingController::class, 'saveMarketplaceAccounts'])->name('marketplaceaccounts.setting.save');
        Route::get('rentaccounts-setting', [SettingController::class, 'rentAccounts'])->name('rentaccounts.setting');
        Route::post('rentaccounts-setting', [SettingController::class, 'saveRentAccounts'])->name('rentaccounts.setting.save');
        Route::get('agreement-setting', [SettingController::class, 'agreementSetting'])->name('agreement.setting');
        Route::get('reminder-setting', [SettingController::class, 'reminderSetting'])->name('reminder.setting');
        Route::get('subscription-reminder-setting', [SettingController::class, 'subscriptionReminderSetting'])->name('subscription.reminder.setting');
        Route::get('cron-setting', [SettingController::class, 'cronSetting'])->name('cron.setting');
        Route::get('affiliate-setting', [SettingController::class, 'affiliateSetting'])->name('affiliate.setting');

        Route::group(['prefix' => 'currency', 'as' => 'currency.'], function () {
            Route::get('/', [CurrencyController::class, 'index'])->name('index');
            Route::post('store', [CurrencyController::class, 'store'])->name('store');
            Route::put('update/{id}', [CurrencyController::class, 'update'])->name('update');
            Route::delete('destroy/{id}', [CurrencyController::class, 'delete'])->name('destroy');
        });

        Route::get('storage-link', [SettingController::class, 'storageLink']);
        Route::get('migrate-seed', [SettingController::class, 'migrateSeed']);
        Route::get('cache-clear', [SettingController::class, 'cacheClear']);
    });

    Route::group(['prefix' => 'mail', 'as' => 'mail.'], function () {
        Route::post('test-send', [MailController::class, 'testSend'])->name('test.send');
    });

    // version update
    Route::get('version-update', [VersionUpdateController::class, 'versionFileUpdate'])->name('file-version-update');
    Route::post('version-update', [VersionUpdateController::class, 'versionFileUpdateStore'])->name('file-version-update-store');
    Route::get('version-update-execute', [VersionUpdateController::class, 'versionUpdateExecute'])->name('file-version-update-execute');
    Route::get('version-delete', [VersionUpdateController::class, 'versionFileUpdateDelete'])->name('file-version-delete');

    Route::group(['prefix' => 'addon', 'as' => 'addon.'], function () {
        Route::get('details/{code}', [AddonUpdateController::class, 'addonSaasDetails'])->name('details')->withoutMiddleware(['addon.update']);
        Route::post('store', [AddonUpdateController::class, 'addonSaasFileStore'])->name('store')->withoutMiddleware(['addon.update']);
        Route::post('execute', [AddonUpdateController::class, 'addonSaasFileExecute'])->name('execute')->withoutMiddleware(['addon.update']);
        Route::post('uninstall/{code}', [AddonUpdateController::class, 'addonSaasUninstall'])->name('uninstall')->withoutMiddleware(['addon.update']);
        Route::get('delete/{code}', [AddonUpdateController::class, 'addonSaasFileDelete'])->name('delete')->withoutMiddleware(['addon.update']);
    });

    Route::resource('product-categories', ProductCategoryController::class)->except(['show', 'create', 'edit']);

    Route::get('/cleanup', function () {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('queue:restart');

        return response()->json(['message' => 'Cleanup completed!']);

    })->name('cleanup');

    Route::get('/migrate', function () {
        Artisan::call('migrate', [
            '--force' => true,
        ]);

        return response()->json(['message' => 'Migration completed!']);

    })->name('migrate');
});
