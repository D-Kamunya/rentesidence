<?php

namespace App\Centresidence;

use App\Centresidence\Console\DispatchDeviceCommandsCommand;
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

        // The device-specific payload codec (config-driven — the only piece that
        // depends on the physical meter).
        $this->app->singleton(\App\Centresidence\Services\ChirpStack\Codec\MeterCodec::class, function ($app) {
            $class = config('centresidence.chirpstack.codec', \App\Centresidence\Services\ChirpStack\Codec\GenericMeterCodec::class);
            return $app->make($class);
        });

        // Resolve the ChirpStack driver from config (simulated default | live).
        $this->app->singleton(\App\Centresidence\Services\ChirpStack\ChirpStackDriver::class, function ($app) {
            return config('centresidence.chirpstack.driver') === 'live'
                ? new \App\Centresidence\Services\ChirpStack\LiveChirpStackDriver(
                    $app->make(\App\Centresidence\Services\ChirpStack\Codec\MeterCodec::class)
                )
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
            DispatchDeviceCommandsCommand::class,
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

        // Plug-and-play: let admins flip the operational drivers from the DB
        // (Admin → Centresidence → Integrations) without touching .env on the
        // shared host. Precedence = DB setting → .env/config default (the lifeline).
        // Secrets (keys/passwords) always stay in .env — never DB-backed.
        $this->applyDriverOverrides();

        // Finance ecosystem event wiring: an approved application creates a
        // facility + repayment schedule.
        Event::listen(ApplicationApproved::class, CreateFacilityForApprovedApplication::class);

        // In-app + SMS notifications across the financing lifecycle (partners had no
        // in-app notifications; owners get platform-paid, ungated SMS on approve/disburse).
        Event::listen(\App\Centresidence\Events\ApplicationSubmitted::class, \App\Centresidence\Listeners\NotifyPartnerOnApplicationSubmitted::class);
        Event::listen(ApplicationApproved::class, \App\Centresidence\Listeners\NotifyOwnerOnApplicationApproved::class);
        Event::listen(FacilityDisbursed::class, \App\Centresidence\Listeners\NotifyOwnerOnFacilityDisbursed::class);

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

            // Drain queued device downlinks (e.g. token credits) to the LoRaWAN
            // network. Every minute so tenant token credits reach meters promptly.
            $schedule->command('centresidence:dispatch-device-commands')
                ->everyMinute()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/centresidence_device_commands.log'));
        });
    }

    /**
     * Override the operational drivers from DB settings when present, so admins
     * can go live from the Integrations page without editing .env. A missing DB
     * setting leaves the .env/config default in force (the fallback lifeline).
     * Wrapped defensively: never break boot on a fresh install / no DB.
     */
    private function applyDriverOverrides(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return;
            }
            $map = [
                'centresidence_collection_driver' => 'centresidence.collections.driver',
                'centresidence_payout_driver'     => 'centresidence.payouts.driver',
                'centresidence_chirpstack_driver' => 'centresidence.chirpstack.driver',
            ];
            foreach ($map as $optionKey => $configKey) {
                $val = getOption($optionKey);
                if (! empty($val)) {
                    config([$configKey => $val]);
                }
            }
        } catch (\Throwable $e) {
            // DB unavailable (e.g. during install/migrate) — keep .env defaults.
        }
    }
}
