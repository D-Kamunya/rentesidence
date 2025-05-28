<?php

use App\Http\Controllers\Owner\CurrencyController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\DocumentController;
use App\Http\Controllers\Owner\ExpenseController;
use App\Http\Controllers\Owner\ExpenseTypeController;
use App\Http\Controllers\Owner\GatewayController;
use App\Http\Controllers\Owner\InformationController;
use App\Http\Controllers\Owner\InvoiceController;
use App\Http\Controllers\Owner\ProductOrderController;
use App\Http\Controllers\Owner\InvoiceRecurringController;
use App\Http\Controllers\Owner\InvoiceTypeController;
use App\Http\Controllers\Owner\KycConfigController;
use App\Http\Controllers\Owner\LocationController;
use App\Http\Controllers\Owner\MaintainerController;
use App\Http\Controllers\Owner\MaintenanceIssueController;
use App\Http\Controllers\Owner\MaintenanceRequestController;
use App\Http\Controllers\Owner\NoticeBoardController;
use App\Http\Controllers\Owner\PropertyController;
use App\Http\Controllers\Owner\ReportController;
use App\Http\Controllers\Owner\SettingController;
use App\Http\Controllers\Owner\TenantController;
use App\Http\Controllers\Owner\TicketController;
use App\Http\Controllers\Owner\TicketTopicController;
use App\Http\Controllers\Tenancy\DomainController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'owner', 'as' => 'owner.', 'middleware' => ['auth', 'owner']], function () {
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('top-search', [DashboardController::class, 'topSearch'])->name('top.search');
    Route::get('notification', [DashboardController::class, 'notification'])->name('notification');
    Route::get('send-login-dets', [TenantController::class, 'sendLoginDets'])->name('send.tenant.logins');

    Route::group(['prefix' => 'property', 'as' => 'property.'], function () {
        Route::get('all-property', [PropertyController::class, 'allProperty'])->name('allProperty');
        Route::get('all-unit', [PropertyController::class, 'allUnit'])->name('allUnit');
        Route::get('own-property', [PropertyController::class, 'ownProperty'])->name('ownProperty');
        Route::get('lease-property', [PropertyController::class, 'leaseProperty'])->name('leaseProperty');
        Route::get('add', [PropertyController::class, 'add'])->name('add');
        Route::get('excel-import', [PropertyController::class, 'propertiesExcelImportForm'])->name('excel.import.form');
        Route::post('excel-import', [PropertyController::class, 'propertiesExcelImport'])->name('excel.import');
        Route::get('show/{id}', [PropertyController::class, 'show'])->name('show');
        Route::get('edit/{id}', [PropertyController::class, 'edit'])->name('edit');
        Route::delete('destroy/{id}', [PropertyController::class, 'destroy'])->name('destroy');
        Route::get('delete/{id}', [PropertyController::class, 'destroy'])->name('delete');
        Route::post('property-information/store', [PropertyController::class, 'propertyInformationStore'])->name('property-information.store');
        Route::post('location/store', [PropertyController::class, 'locationStore'])->name('location.store');
        Route::post('unit/store', [PropertyController::class, 'unitStore'])->name('unit.store');
        Route::post('unit/edit', [PropertyController::class, 'unitEdit'])->name('unit.edit');
        Route::get('unit/details/{id}', [PropertyController::class, 'unitDetails'])->name('unit.details');
        Route::delete('unit/delete/{id}', [PropertyController::class, 'unitDelete'])->name('unit.delete');
        Route::post('rent-charge/store', [PropertyController::class, 'rentChargeStore'])->name('rentCharge.store');
        Route::post('image/store/{id?}', [PropertyController::class, 'imageStore'])->name('image.store');
        Route::get('image/delete/{id}', [PropertyController::class, 'imageDelete'])->name('image.delete');
        Route::post('thumbnail-image/update/{id}', [PropertyController::class, 'thumbnailImageUpdate'])->name('thumbnailImage.update');

        Route::get('get-property-information', [PropertyController::class, 'getPropertyInformation'])->name('getPropertyInformation');
        Route::get('get-location', [PropertyController::class, 'getLocation'])->name('getLocation');
        Route::get('get-unit', [PropertyController::class, 'getUnitByPropertyId'])->name('getUnitByPropertyId');
        Route::get('get-unit-by-property-ids', [PropertyController::class, 'getUnitByPropertyIds'])->name('getUnitByPropertyIds');
        Route::get('get-rent-charge', [PropertyController::class, 'getRentCharge'])->name('getRentCharge');
        Route::get('get-property-units', [PropertyController::class, 'getPropertyUnits'])->name('getPropertyUnits');
        Route::get('get-property-with-units-by-id', [PropertyController::class, 'getPropertyWithUnitsById'])->name('getPropertyWithUnitsById');
    });

    Route::group(['prefix' => 'tenant', 'as' => 'tenant.'], function () {
        Route::get('/', [TenantController::class, 'index'])->name('index');
        Route::get('create', [TenantController::class, 'create'])->name('create');
        Route::get('edit/{id}', [TenantController::class, 'edit'])->name('edit');
        Route::post('store', [TenantController::class, 'store'])->name('store');
        Route::get('document/delete/{id}', [TenantController::class, 'documentDestroy'])->name('document.destroy');
        Route::get('details/{id}', [TenantController::class, 'details'])->name('details');
        Route::post('close-history-store/{id}', [TenantController::class, 'closeHistoryStore'])->name('close.history.store');
        Route::post('delete', [TenantController::class, 'delete'])->name('delete');
    });

    Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
        Route::get('/', [ProductOrderController::class, 'index'])->name('index');
        // Route::get('print/{id}', [InvoiceController::class, 'details'])->name('print');
        // Route::get('pay/{id}', [InvoiceController::class, 'pay'])->name('pay');
        // Route::get('get-currency-by-gateway', [InvoiceController::class, 'getCurrencyByGateway'])->name('get.currency');
    });

    Route::group(['prefix' => 'information', 'as' => 'information.'], function () {
        Route::get('/', [InformationController::class, 'index'])->name('index');
        Route::post('store', [InformationController::class, 'store'])->name('store');
        Route::get('get-info', [InformationController::class, 'getInfo'])->name('get.info'); // ajax
        Route::get('delete/{id}', [InformationController::class, 'delete'])->name('delete');
    });

    Route::group(['prefix' => 'maintainer', 'as' => 'maintainer.'], function () {
        Route::get('/', [MaintainerController::class, 'index'])->name('index');
        Route::post('store', [MaintainerController::class, 'store'])->name('store');
        Route::get('get-info', [MaintainerController::class, 'getInfo'])->name('get.info'); // ajax
        Route::get('delete/{id}', [MaintainerController::class, 'delete'])->name('delete');
    });

    Route::group(['prefix' => 'maintenance-request', 'as' => 'maintenance-request.'], function () {
        Route::get('/', [MaintenanceRequestController::class, 'index'])->name('index');
        Route::post('store', [MaintenanceRequestController::class, 'store'])->name('store');
        Route::get('get-info', [MaintenanceRequestController::class, 'getInfo'])->name('get.info'); // ajax
        Route::post('status-change', [MaintenanceRequestController::class, 'statusChange'])->name('status.change');
        Route::get('delete/{id}', [MaintenanceRequestController::class, 'delete'])->name('delete');
    });

    Route::group(['prefix' => 'notice-board', 'as' => 'noticeboard.'], function () {
        Route::get('/', [NoticeBoardController::class, 'index'])->name('index');
        Route::post('store', [NoticeBoardController::class, 'store'])->name('store');
        Route::get('get-info', [NoticeBoardController::class, 'getInfo'])->name('get.info'); // ajax
        Route::get('delete/{id}', [NoticeBoardController::class, 'delete'])->name('delete');
    });

    Route::group(['prefix' => 'expense', 'as' => 'expense.'], function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::get('details/{id}', [ExpenseController::class, 'details'])->name('details');
        Route::post('store', [ExpenseController::class, 'store'])->name('store');
        Route::post('update/{id}', [ExpenseController::class, 'update'])->name('update');
        Route::get('destroy/{id}', [ExpenseController::class, 'destroy'])->name('destroy');
        Route::post('new-expense-type', [ExpenseController::class, 'expenseTypeStore'])->name('expenseType.store');
    });

    Route::group(['prefix' => 'documents', 'as' => 'documents.'], function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('status/{id}', [DocumentController::class, 'statusChange'])->name('status');
        Route::get('get-info', [DocumentController::class, 'getInfo'])->name('get.info'); // ajax
        Route::post('reject-reason', [DocumentController::class, 'rejectReasonStore'])->name('reject.reason.store');
        Route::get('delete/{id}', [DocumentController::class, 'delete'])->name('delete');
    });

    Route::group(['prefix' => 'invoice', 'as' => 'invoice.'], function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('paid', [InvoiceController::class, 'paidInvoiceIndex'])->name('paid');
        Route::get('pending', [InvoiceController::class, 'pendingInvoiceIndex'])->name('pending');
        Route::get('bank-pending', [InvoiceController::class, 'bankPendingInvoice'])->name('bank.pending');
        Route::get('overdue', [InvoiceController::class, 'overDueInvoiceIndex'])->name('overdue');
        Route::get('details/{id}', [InvoiceController::class, 'details'])->name('details');
        Route::post('store', [InvoiceController::class, 'store'])->name('store');
        Route::put('update/{id}', [InvoiceController::class, 'update'])->name('update');
        Route::get('destroy/{id}', [InvoiceController::class, 'destroy'])->name('destroy');
        Route::get('types', [InvoiceController::class, 'types'])->name('types');
        Route::post('payment/status', [InvoiceController::class, 'paymentStatus'])->name('payment.status');
        Route::get('print/{id}', [InvoiceController::class, 'print'])->name('print');
        Route::post('sendNotification', [InvoiceController::class, 'sendNotification'])->name('send.notification');
        // recurring
        Route::group(['prefix' => 'recurring-setting', 'as' => 'recurring-setting.'], function () {
            Route::get('/', [InvoiceRecurringController::class, 'index'])->name('index');
            Route::post('store', [InvoiceRecurringController::class, 'store'])->name('store');
            Route::get('details/{id}', [InvoiceRecurringController::class, 'details'])->name('details');
            Route::get('destroy/{id}', [InvoiceRecurringController::class, 'destroy'])->name('destroy');
        });
    });

    Route::group(['prefix' => 'location', 'as' => 'location.'], function () {
        Route::get('country-list', [LocationController::class, 'countryList'])->name('country.list');
        Route::get('state-list', [LocationController::class, 'stateList'])->name('state.list');
        Route::get('city-list', [LocationController::class, 'cityList'])->name('city.list');
    });

    Route::get('cache-clear', [SettingController::class, 'cache_clear'])->name('cache.clear');

    Route::group(['prefix' => 'ticket', 'as' => 'ticket.'], function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('details/{id}', [TicketController::class, 'details'])->name('details');
        Route::post('reply', [TicketController::class, 'reply'])->name('reply');
        Route::get('status-change', [TicketController::class, 'statusChange'])->name('status.change');
    });

    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
        Route::get('earning', [ReportController::class, 'earning'])->name('earning');
        Route::get('loss-profit', [ReportController::class, 'lossProfitByMonth'])->name('loss-profit.by.month');
        Route::get('expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('lease', [ReportController::class, 'lease'])->name('lease');
        Route::get('occupancy', [ReportController::class, 'occupancy'])->name('occupancy');
        Route::get('maintenance', [ReportController::class, 'maintenance'])->name('maintenance');
        Route::get('tenant', [ReportController::class, 'tenant'])->name('tenant');
    });

    // Start:: Setting
    Route::group(['prefix' => 'setting', 'as' => 'setting.'], function () {
        Route::get('general-setting', [SettingController::class, 'generalSetting'])->name('general-setting');
        Route::post('general-settings-update', [SettingController::class, 'generalSettingUpdate'])->name('general-setting.update');
        Route::get('color-setting', [SettingController::class, 'colorSetting'])->name('color-setting');
        Route::get('tax-setting', [SettingController::class, 'taxSetting'])->name('tax-setting');
        Route::post('tax-setting-update', [SettingController::class, 'taxSettingUpdate'])->name('tax-update');
        Route::get('smtp-setting', [SettingController::class, 'smtpSetting'])->name('smtp.setting');
        Route::post('general-settings-env-update', [SettingController::class, 'generalSettingEnvUpdate'])->name('general-setting-env.update');

        //Start:: Currency Settings
        Route::group(['prefix' => 'currency', 'as' => 'currency.'], function () {
            Route::get('', [CurrencyController::class, 'index'])->name('index');
            Route::post('store', [CurrencyController::class, 'store'])->name('store');
            Route::put('update/{id}', [CurrencyController::class, 'update'])->name('update');
            Route::delete('destroy/{id}', [CurrencyController::class, 'delete'])->name('destroy');
        });
        //End:: Currency Settings

        Route::group(['prefix' => 'gateway', 'as' => 'gateway.'], function () {
            Route::get('/', [GatewayController::class, 'index'])->name('index');
            Route::post('store', [GatewayController::class, 'store'])->name('store');
            Route::get('get-info', [GatewayController::class, 'getInfo'])->name('get.info');
            Route::get('delete/{gateway}', [GatewayController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'document-config', 'as' => 'document-config.'], function () {
            Route::get('/', [KycConfigController::class, 'index'])->name('index');
            Route::post('store', [KycConfigController::class, 'store'])->name('store');
            Route::get('get-info', [KycConfigController::class, 'getInfo'])->name('get.info'); // ajax
            Route::delete('delete/{id}', [KycConfigController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'maintenance-issue', 'as' => 'maintenance-issue.'], function () {
            Route::get('/', [MaintenanceIssueController::class, 'index'])->name('index');
            Route::post('store', [MaintenanceIssueController::class, 'store'])->name('store');
            Route::get('get-info', [MaintenanceIssueController::class, 'getInfo'])->name('get.info'); // ajax
            Route::delete('delete/{id}', [MaintenanceIssueController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'ticket-topic', 'as' => 'ticket-topic.'], function () {
            Route::get('/', [TicketTopicController::class, 'index'])->name('index');
            Route::post('store', [TicketTopicController::class, 'store'])->name('store');
            Route::delete('destroy/{id}', [TicketTopicController::class, 'destroy'])->name('destroy');
        });

        Route::group(['prefix' => 'expense-type', 'as' => 'expense-type.'], function () {
            Route::get('/', [ExpenseTypeController::class, 'index'])->name('index');
            Route::post('store', [ExpenseTypeController::class, 'store'])->name('store');
            Route::delete('destroy/{id}', [ExpenseTypeController::class, 'destroy'])->name('destroy');
        });

        Route::group(['prefix' => 'invoice-type', 'as' => 'invoice-type.'], function () {
            Route::get('/', [InvoiceTypeController::class, 'index'])->name('index');
            Route::post('store', [InvoiceTypeController::class, 'store'])->name('store');
            Route::delete('destroy/{id}', [InvoiceTypeController::class, 'destroy'])->name('destroy');
        });
    });
    // setting end

    // Tenancy setting
    Route::group(['prefix' => 'domain', 'as' => 'domain.'], function () {
        Route::get('/', [DomainController::class, 'index'])->name('index');
        Route::post('store', [DomainController::class, 'store'])->name('store');
        Route::get('info', [DomainController::class, 'info'])->name('info');
    });

    Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('create', [ProductController::class, 'create'])->name('create');
        Route::post('store', [ProductController::class, 'store'])->name('store');
        Route::get('edit/{product}', [ProductController::class, 'edit'])->name('edit');
        Route::put('update/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('destroy/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });
});
