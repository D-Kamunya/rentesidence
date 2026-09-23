<?php

namespace App\Services\Sms;

use App\Models\Package;
use App\Models\Owner;
use Illuminate\Support\Facades\Log;

class PackageSmsCreditsService
{
    /**
     * Grant monthly SMS credits to an owner when a subscription is activated or renewed.
     * Safe to call on every activation — zero-credit packages are a no-op.
     *
     * @param int $ownerUserId  — the owner's user_id (auth user)
     * @param int $packageId    — the Package model id
     */
    public static function grantOnActivation(int $ownerUserId, int $packageId): void
    {
        try {
            $package = Package::find($packageId);

            if (!$package) {
                Log::warning("PackageSmsCreditsService: package {$packageId} not found.");
                return;
            }

            $credits = (int) ($package->monthly_sms_credits ?? 0);

            // Always RESET the granted allowance to this package's amount for the new period —
            // including 0 (free / transaction tier), which clears any leftover grant. Purchased
            // credits are never touched. (No rollover of unspent grant — the agreed policy.)
            SmsCreditsService::grantPackageCredits($ownerUserId, $credits, $package->name);

        } catch (\Exception $e) {
            // Never let this break the subscription flow
            Log::error("PackageSmsCreditsService: failed for owner_user_id={$ownerUserId}, package_id={$packageId} — " . $e->getMessage());
        }
    }
}