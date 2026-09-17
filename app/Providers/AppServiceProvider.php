<?php

namespace App\Providers;

use App\Models\Language;
use App\Models\Setting;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Builder;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();

        // Share the "ownerless" (standalone Tenant Helper) flag to every tenant view so the sidebar,
        // dashboard and guards all read one source of truth. Cheap: ->tenant is loaded once/request.
        \Illuminate\Support\Facades\View::composer('tenant.*', function ($view) {
            $view->with('ownerless', optional(auth()->user())->isOwnerlessTenant() ?? false);
        });
        try {
            Builder::defaultStringLength(191);
            $connection = DB::connection()->getPdo();
            if ($connection) {
                $allOptions = [];
                $allOptions['settings'] = Setting::all()->pluck('option_value', 'option_key')->toArray();
                config($allOptions);
                config(['app.name' => getOption('app_name')]);

                // Fetch the default currency from the database
                $defaultCurrencySetting = getOption('currency_id');
                session(['default_currency' => config('app.default_currency')]);
                if ($defaultCurrencySetting) {
                    $currency = Currency::where('id', $defaultCurrencySetting)->first();
                    $defaultCurrency = $currency->symbol;
                    config(['app.default_currency' => $defaultCurrency]);
                    session(['default_currency' => $defaultCurrency]);
                }
            }
        } catch (\Exception $e) {
            //
        }
    }
}
