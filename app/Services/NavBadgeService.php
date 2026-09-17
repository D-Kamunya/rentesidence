<?php

namespace App\Services;

use App\Models\AffiliateWithdrawal;
use App\Models\HouseHuntApplication;
use App\Models\Invoice;

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

    /** Admin sidebar: pending affiliate withdrawals, referral payouts ready to pay. */
    public function forAdmin(): array
    {
        return [
            'affiliate_withdrawals' => $this->safe(fn () => AffiliateWithdrawal::where('status', AFFILIATE_WITHDRAWAL_PENDING)->count()),
            'referral_payouts'      => $this->safe(function () {
                $svc = app(LandlordReferralService::class);
                return $svc->enabled() ? $svc->tenantsEligibleForPayout()->count() : 0;
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
