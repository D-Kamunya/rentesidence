<?php

namespace App\Centresidence\Console;

use App\Centresidence\Services\BillingCycleService;
use App\Centresidence\Support\Money;
use App\Models\Property;
use App\Models\SubscriptionOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Runs the Centresidence monthly billing cycle (infrastructure + commission
 * invoices). Scheduled on the configured cycle day; can also be run manually
 * for a specific month.
 *
 *   php artisan centresidence:run-billing-cycle
 *   php artisan centresidence:run-billing-cycle --month=2026-06
 *
 * The legacy-subscription bridge is wired here (the command is the integration
 * point): a resolver maps each property to its owner's monthly plan price so the
 * commission invoice bundles plan + modules. The plan amount is attributed ONCE
 * per owner (first property iterated) to avoid double-counting across an owner's
 * properties.
 */
class RunBillingCycleCommand extends Command
{
    protected $signature = 'centresidence:run-billing-cycle {--month= : Billing month (YYYY-MM), defaults to current month}';

    protected $description = 'Generate Centresidence infrastructure & commission invoices for the billing month';

    public function handle(BillingCycleService $service): int
    {
        if (! config('centresidence.enabled', true)) {
            $this->warn('Centresidence is disabled (centresidence.enabled=false); skipping billing cycle.');

            return self::SUCCESS;
        }

        $month = $this->option('month')
            ? Carbon::createFromFormat('Y-m', $this->option('month'))->startOfMonth()
            : Carbon::now()->startOfMonth();

        $this->info("Running Centresidence billing cycle for {$month->format('Y-m')}…");

        $summary = $service->runForMonth($month, $this->subscriptionResolver());

        $this->table(
            ['Month', 'Commission invoices', 'Infrastructure invoices'],
            [[$summary['month'], $summary['commission_invoices'], $summary['infrastructure_invoices']]]
        );

        return self::SUCCESS;
    }

    /**
     * Legacy bridge: property → owner's monthly plan price (what they actually
     * pay, from the latest paid subscription order), attributed once per owner.
     */
    private function subscriptionResolver(): callable
    {
        $seen = [];

        return function (Property $property) use (&$seen): Money {
            $ownerId = (int) $property->owner_user_id;
            if (! $ownerId || isset($seen[$ownerId])) {
                return Money::zero();
            }
            $seen[$ownerId] = true;

            $amount = (float) (SubscriptionOrder::where('user_id', $ownerId)
                ->where('payment_status', 1)->latest()->value('amount') ?? 0);

            return Money::fromDecimal(number_format($amount, 2, '.', ''));
        };
    }
}
