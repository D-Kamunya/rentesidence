<?php

namespace App\Services;

use App\Models\DepositSettlement;
use App\Models\KycVerification;
use App\Models\VacationNotice;

/**
 * The single source of truth for "this tenancy needs the OWNER's attention" — surfaced identically
 * on the tenants index cards, the tenant-detail sidenav dots, and (potentially) the dashboard.
 *
 * Three signals only (deliberate — arrears has its own coloured-invoice surfaces):
 *   notice     → a pending notice-to-vacate awaiting acknowledgement       (Payments & Deposit tab)
 *   settlement → a reported deposit settlement the owner hasn't answered    (Payments & Deposit tab)
 *   documents  → KYC document(s) submitted and awaiting the owner's review  (Documents tab)
 */
class TenantAttentionService
{
    /**
     * BATCH — one query per signal for a whole page of tenants (no N+1). Returns only the tenants
     * that HAVE at least one signal: [tenant_id => ['notice'=>bool,'settlement'=>bool,'documents'=>bool]].
     *
     * @param array<int> $tenantIds
     * @return array<int,array{notice:bool,settlement:bool,documents:bool}>
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
            if ($n || $s || $d) {
                $out[$id] = ['notice' => $n, 'settlement' => $s, 'documents' => $d];
            }
        }
        return $out;
    }

    /** Single tenant — always returns the full shape (all false when nothing needs attention). */
    public function forTenant($tenantId): array
    {
        $a = $this->forTenants([(int) $tenantId]);
        return $a[(int) $tenantId] ?? ['notice' => false, 'settlement' => false, 'documents' => false];
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

    /** Any attention at all? */
    public function any(array $sig): bool
    {
        return !empty($sig['notice']) || !empty($sig['settlement']) || !empty($sig['documents']);
    }
}
