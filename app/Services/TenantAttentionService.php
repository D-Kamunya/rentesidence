<?php

namespace App\Services;

use App\Models\DepositSettlement;
use App\Models\KycVerification;
use App\Models\VacationNotice;

/**
 * The single source of truth for "this tenancy needs the OWNER's attention" — surfaced identically
 * on the tenants index cards, the tenant-detail sidenav dots, and (potentially) the dashboard.
 *
 * Four signals only (deliberate — arrears has its own coloured-invoice surfaces):
 *   notice      → a pending notice-to-vacate awaiting acknowledgement       (Payments & Deposit tab)
 *   settlement  → a reported deposit settlement the owner hasn't answered    (Payments & Deposit tab)
 *   documents   → KYC document(s) submitted and awaiting the owner's review  (Documents tab)
 *   ready_close → an acknowledged notice whose move-out date has arrived —
 *                 the tenancy should be finalized with Close Tenant           (Profile tab)
 */
class TenantAttentionService
{
    /**
     * BATCH — one query per signal for a whole page of tenants (no N+1). Returns only the tenants
     * that HAVE at least one signal: [tenant_id => ['notice'=>bool,'settlement'=>bool,'documents'=>bool]].
     *
     * @param array<int> $tenantIds
     * @return array<int,array{notice:bool,settlement:bool,documents:bool,ready_close:bool}>
     */
    public function forTenants(array $tenantIds): array
    {
        $tenantIds = array_values(array_unique(array_map('intval', $tenantIds)));
        if (empty($tenantIds)) {
            return [];
        }

        $notice = VacationNotice::whereIn('tenant_id', $tenantIds)
            ->where('status', VacationNotice::STATUS_PENDING)
            ->pluck('tenant_id')->flip();

        // Acknowledged notice + move-out date reached (or within the prompt window) = ready to close.
        // Mirrors VacationNoticeService::readyToCloseForOwner; guard against a closed tenancy still
        // carrying an acknowledged notice (closing doesn't retire the notice yet — Slice 3).
        $readyClose = VacationNotice::whereIn('tenant_id', $tenantIds)
            ->where('status', VacationNotice::STATUS_ACKNOWLEDGED)
            ->whereDate('intended_move_out_date', '<=', now()->addDays(VacationNoticeService::CLOSE_PROMPT_WITHIN_DAYS))
            ->whereHas('tenant', fn ($q) => $q->where('status', '!=', TENANT_STATUS_CLOSE))
            ->pluck('tenant_id')->flip();

        $settle = DepositSettlement::whereIn('tenant_id', $tenantIds)
            ->where('status', DepositSettlement::STATUS_DISPUTED)
            ->whereNull('owner_responded_at')
            ->pluck('tenant_id')->flip();

        $docs = KycVerification::whereIn('tenant_id', $tenantIds)
            ->where('status', KYC_STATUS_PENDING)
            ->pluck('tenant_id')->flip();

        $out = [];
        foreach ($tenantIds as $id) {
            $n = $notice->has($id);
            $s = $settle->has($id);
            $d = $docs->has($id);
            $r = $readyClose->has($id);
            if ($n || $s || $d || $r) {
                $out[$id] = ['notice' => $n, 'settlement' => $s, 'documents' => $d, 'ready_close' => $r];
            }
        }
        return $out;
    }

    /** Single tenant — always returns the full shape (all false when nothing needs attention). */
    public function forTenant($tenantId): array
    {
        $a = $this->forTenants([(int) $tenantId]);
        return $a[(int) $tenantId] ?? ['notice' => false, 'settlement' => false, 'documents' => false, 'ready_close' => false];
    }

    /** Does the Payments & Deposit tab carry attention? */
    public function paymentsTab(array $sig): bool
    {
        return !empty($sig['notice']) || !empty($sig['settlement']);
    }

    /** Does the Documents tab carry attention? */
    public function documentsTab(array $sig): bool
    {
        return !empty($sig['documents']);
    }

    /** Does the Profile tab carry attention (a tenancy ready to be closed)? */
    public function profileTab(array $sig): bool
    {
        return !empty($sig['ready_close']);
    }

    /** Any attention at all? */
    public function any(array $sig): bool
    {
        return !empty($sig['notice']) || !empty($sig['settlement']) || !empty($sig['documents']) || !empty($sig['ready_close']);
    }
}
