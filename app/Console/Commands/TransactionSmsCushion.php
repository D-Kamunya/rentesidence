<?php

namespace App\Console\Commands;

use App\Services\Sms\PackageSmsCreditsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Monthly SMS cushion for TRANSACTION-mode owners.
 *
 * Subscription owners get their SMS allowance reset on each renewal (PackageSmsCreditsService on
 * activation). Transaction has no monthly renewal event, so without this its flat cushion would be a
 * one-time grant at mode-switch. This re-grants it each month: it RESETS the granted (expiring)
 * allowance to the Transaction plan's amount — unused grant does NOT roll over, so an owner can
 * never stockpile a million free credits — while PURCHASED credits are untouched (they never
 * expire). Idempotent: a reset is a set-to-amount, so a re-run in the same period is harmless.
 */
class TransactionSmsCushion extends Command
{
    protected $signature = 'sms:transaction-cushion';

    protected $description = 'Reset the monthly SMS cushion for Transaction-mode owners (granted resets, purchased untouched).';

    public function handle(): int
    {
        // Latest ACTIVE package per owner, filtered to the Transaction pricing model — mirrors the
        // admin dashboard's "owners by current plan" pattern (so stale/old owner_packages don't count).
        $rows = DB::table('owner_packages')
            ->whereIn('owner_packages.id', function ($q) {
                $q->selectRaw('MAX(id)')->from('owner_packages')->where('status', ACTIVE)->groupBy('user_id');
            })
            ->join('packages', 'owner_packages.package_id', '=', 'packages.id')
            ->join('owners', 'owner_packages.user_id', '=', 'owners.user_id')
            ->whereNull('owners.deleted_at')
            ->where('packages.pricing_model', 'transaction')
            ->select('owner_packages.user_id', 'owner_packages.package_id')
            ->get();

        $count = 0;
        foreach ($rows as $row) {
            // Resets the granted allowance to the Transaction plan's monthly_sms_credits; purchased
            // credits are never touched. Fail-safe inside grantOnActivation.
            PackageSmsCreditsService::grantOnActivation((int) $row->user_id, (int) $row->package_id);
            $count++;
        }

        $this->info("Transaction SMS cushion reset for {$count} owner(s).");

        return self::SUCCESS;
    }
}
