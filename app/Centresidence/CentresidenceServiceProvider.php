<?php

namespace App\Centresidence;

use App\Centresidence\Console\ProcessCollectionsCommand;
use App\Centresidence\Console\RemitPartnersCommand;
use App\Centresidence\Console\RunBillingCycleCommand;
use App\Centresidence\Console\SimulateCommand;
use App\Centresidence\Console\SnapshotFinanceAnalyticsCommand;
use App\Centresidence\Events\ApplicationApproved;
use App\Centresidence\Events\FacilityDisbursed;
use App\Centresidence\Events\TokenPurchased;
use App\Centresidence\Listeners\CollectDownPaymentOnDisbursement;
use App\Centresidence\Listeners\CreateFacilityForApprovedApplication;
use App\Centresidence\Listeners\CreditOwnerWalletOnTokenPurchase;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the Centresidence Infrastructure & Finance OS module.
 *
 * Keeps the new platform decoupled from the live rental SaaS: the module owns
 * its own migration path and configuration, and registers its own events,
 * console commands and bindings as the engines come online. Nothing here
 * touches or overrides legacy behaviour.
 */
class CentresidenceServiceProvider extends ServiceProvider
{
    /**
     * Register module services/bindings.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/centresidence.php',
            'centresidence'
        );

        // Resolve the ChirpStack driver from config (simulated default | live).
        $this->app->singleton(\App\Centresidence\Services\ChirpStack\ChirpStackDriver::class, function () {
            return config('centresidence.chirpstack.driver') === 'live'
                ? new \App\Centresidence\Services\ChirpStack\LiveChirpStackDriver()
                : new \App\Centresidence\Services\ChirpStack\SimulatedChirpStackDriver();
        });

        // Register console commands during the register phase (before any
        // provider boot), so the Artisan::starting callback is queued even if
        // an earlier provider builds the console Application during boot.
        $this->commands([
            RunBillingCycleCommand::class,
            SimulateCommand::class,
            ProcessCollectionsCommand::class,
            SnapshotFinanceAnalyticsCommand::class,
            RemitPartnersCommand::class,
        ]);
    }

    /**
     * Bootstrap module: migrations now; events, commands and routes are wired
     * in as each work package lands.
     */
    public function boot(): void
    {
        // Module-owned migrations live alongside the code, not in the shared
        // database/migrations path, so the module stays self-contained while
        // still being picked up by `php artisan migrate`.
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Allow publishing the config for environment-specific overrides.
        $this->publishes([
            __DIR__ . '/../../config/centresidence.php' => config_path('centresidence.php'),
        ], 'centresidence-config');

        // Finance ecosystem event wiring: an approved application creates a
        // facility + repayment schedule.
        Event::listen(ApplicationApproved::class, CreateFacilityForApprovedApplication::class);

        // On disbursement, collect the owner's down-payment (partial financing)
        // to Centresidence as the installer/payee. No-op when contribution = 0.
        Event::listen(FacilityDisbursed::class, CollectDownPaymentOnDisbursement::class);

        // Credit the owner's net token revenue to their wallet on each sale.
        Event::listen(TokenPurchased::class, CreditOwnerWalletOnTokenPurchase::class);

        // Schedule the monthly billing cycle without touching the legacy
        // Console\Kernel. Runs on the configured cycle day.
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $cycleDay = (int) config('centresidence.billing.cycle_day', 1);

            $schedule->command('centresidence:run-billing-cycle')
                ->monthlyOn($cycleDay, '02:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/centresidence_billing_cycle.log'));

            // Daily collections sweep (overdue → penalty → default) and the
            // finance analytics snapshot.
            $schedule->command('centresidence:process-collections')
                ->dailyAt('03:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/centresidence_collections.log'));

            $schedule->command('centresidence:snapshot-finance-analytics')
                ->dailyAt('03:30')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/centresidence_analytics.log'));

            // Settle collected facility repayments to partners on their cadence.
            $schedule->command('centresidence:remit-partners')
                ->dailyAt('04:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/centresidence_remittances.log'));
        });
    }
}
