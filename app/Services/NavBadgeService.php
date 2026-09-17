<?php

namespace App\Services;

use App\Models\AffiliateWithdrawal;
use App\Models\HouseHuntApplication;
use App\Models\Invoice;
use App\Models\LandlordReferral;

/**
 * Sidebar count badges — the actionable "you have N things waiting" numbers on nav links,
 * per role. Each entry is a single, cheap, correct query (reusing the same logic the target
 * page uses), computed once per request via a view composer on each role's sidebar.
 *
 * A badge only means something you can ACT on, so a count of zero renders nothing. Every query
 * is schema-guarded so a bare/partial install (or a test rendering a sidebar) degrades to 0
 * rather than throwing.
 */
class NavBadgeService
{
    /** Owner sidebar: pending applications to review, deposits due for settlement. */
    public function forOwner(int $ownerUserId): array
    {
        return [
            'applications' => $this->safe(fn () => HouseHuntApplication::whereHas(
                'propertyUnit.property',
                fn ($q) => $q->where('owner_user_id', $ownerUserId)
            )->where('status', HOUSE_HUNT_APPLICATION_PENDING)->count()),

            'deposits_due' => $this->safe(fn () => app(DepositService::class)->dueForSettlementCount($ownerUserId)),
        ];
    }

    /** Tenant sidebar: unpaid invoices. */
    public function forTenant(int $tenantRecordId): array
    {
        return [
            'invoices_unpaid' => $this->safe(fn () => Invoice::where('tenant_id', $tenantRecordId)
                ->where('status', INVOICE_STATUS_PENDING)->count()),
        ];
    }

    /**
     * Admin sidebar: pending affiliate withdrawals, and referral items needing attention —
     * owner sign-up requests to onboard PLUS reward payouts ready to pay.
     */
    public function forAdmin(): array
    {
        return [
            'affiliate_withdrawals' => $this->safe(fn () => AffiliateWithdrawal::where('status', AFFILIATE_WITHDRAWAL_PENDING)->count()),
            'referral_payouts'      => $this->safe(function () {
                $svc = app(LandlordReferralService::class);
                if (! $svc->enabled()) {
                    return 0;
                }

                // A referred landlord filled the form and is waiting to be onboarded into an owner.
                $onboard = LandlordReferral::where('status', LandlordReferral::STATUS_LEAD_CREATED)
                    ->whereNull('owner_id')
                    ->whereHas('lead', fn ($q) => $q->whereNull('owner_id'))
                    ->count();

                return $onboard + $svc->tenantsEligibleForPayout()->count();
            }),
        ];
    }

    /** Run a count, returning 0 on any error (missing table/column on a bare install, etc.). */
    private function safe(callable $fn): int
    {
        try {
            return (int) $fn();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
