<?php

namespace App\Console\Commands;

use App\Services\LandlordReferralService;
use Illuminate\Console\Command;

/**
 * The "OR cumulative revenue ≥ threshold" half of the invite-a-landlord real-customer bar.
 *
 * The paid-subscription hook confirms rewards instantly for owners who buy a plan. This daily
 * sweep catches the other case: a referred owner who monetizes WITHOUT a subscription (rent
 * commission, marketplace, tokens) and crosses the revenue threshold. Idempotent — a reward
 * confirms once — so re-running is safe.
 */
class EvaluateReferralRevenue extends Command
{
    protected $signature   = 'referrals:evaluate-revenue';
    protected $description = 'Confirm invite-a-landlord rewards for referred owners who have crossed the revenue threshold';

    public function handle(LandlordReferralService $referrals): int
    {
        if (! $referrals->enabled()) {
            $this->info('Invite-a-landlord funnel is disabled — nothing to do.');
            return self::SUCCESS;
        }

        $confirmed = $referrals->confirmEligibleByRevenue();
        $this->info("Referral rewards confirmed by revenue threshold: {$confirmed}");

        return self::SUCCESS;
    }
}
