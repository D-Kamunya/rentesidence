<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use Illuminate\Support\Carbon;

/**
 * An owner's INFRASTRUCTURE billing standing — the infra half of the unified
 * "account standing" (the plan half lives in the legacy SubscriptionService).
 *
 * Reads unpaid module-infra bills (CentresidenceCommissionInvoice) and classifies:
 *   - current  → nothing infra owed
 *   - due      → infra owed, still within the grace window
 *   - overdue  → infra owed past grace (drives the readonly/degraded gate + the
 *                metered token-recovery fallback, consistent with CommissionFallbackService)
 *
 * The "infra amount" is the metered + non-metered module cost only — NOT the plan
 * (`subscription_amount`), which is billed/collected on the subscription side.
 */
class OwnerBillingStandingService
{
    public function infraStanding(int $ownerUserId, ?Carbon $asOf = null): array
    {
        $none = ['state' => 'current', 'amount_due' => 0.0, 'invoice_count' => 0, 'oldest_billing_month' => null];

        if (! config('centresidence.enabled', true)) {
            return $none;
        }

        $asOf      = ($asOf ?? Carbon::now())->copy();
        $graceDays = (int) config('centresidence.billing.commission_grace_days', 7);

        $unpaid = CentresidenceCommissionInvoice::query()
            ->where('owner_id', $ownerUserId)
            ->unpaid()
            ->get();

        // Sum only the infra portion (metered + non-metered), not the plan amount.
        $amountDue = (float) $unpaid->sum(
            fn ($inv) => (float) $inv->metered_commission_total + (float) $inv->non_metered_commission_total
        );

        if ($unpaid->isEmpty() || $amountDue <= 0) {
            return $none;
        }

        // Overdue if the oldest unpaid bill's month predates the grace window —
        // same threshold the token-recovery fallback uses, so they never disagree.
        $oldest    = Carbon::parse($unpaid->min(fn ($inv) => $inv->billing_month))->startOfDay();
        $threshold = $asOf->copy()->subDays($graceDays)->startOfDay();
        $isOverdue = $oldest->lt($threshold);

        return [
            'state'                => $isOverdue ? 'overdue' : 'due',
            'amount_due'           => round($amountDue, 2),
            'invoice_count'        => $unpaid->count(),
            'oldest_billing_month' => $oldest,
        ];
    }

    /**
     * Whether unpaid infra should trigger the READONLY gate — cadence-aware (the
     * merge decision): infra is bundled with the plan for MONTHLY owners, so it
     * only blocks once the plan itself has lapsed (renewal is where it's paid) —
     * never mid-cycle. YEARLY / plan-less owners pay infra standalone monthly, so
     * they block on the raw grace (overdue). Nothing owed → never blocks.
     */
    public function isReadonly(int $ownerUserId, ?Carbon $asOf = null): bool
    {
        $infra = $this->infraStanding($ownerUserId, $asOf);
        if ($infra['amount_due'] <= 0) {
            return false;
        }

        $cadence = $this->planCadence($ownerUserId);
        if ($cadence && $cadence['monthly']) {
            return $cadence['expired']; // monthly: infra blocks only when the plan has lapsed
        }

        return $infra['state'] === 'overdue'; // yearly / standalone / unknown → own grace
    }

    /**
     * Whether the owner's infra is currently ENFORCEABLE — i.e. we may recover it
     * (token fallback) or gate on it. This is the cadence half of option A shared
     * by the readonly gate and the fallback: for a MONTHLY owner infra rides with
     * the plan renewal, so it becomes enforceable only once the plan has lapsed;
     * for YEARLY / plan-less / non-subscription owners it follows its own grace
     * and is always enforceable when overdue. Returning true here does NOT mean
     * anything is owed — callers still check the amount/overdue state.
     *
     * Guarded (planCadence self-guards) → true wherever cadence is unknown, so the
     * fallback keeps its pre-cadence behaviour in the isolated sandbox/tests.
     */
    public function mayEnforceInfra(int $ownerUserId): bool
    {
        $cadence = $this->planCadence($ownerUserId);

        if ($cadence && $cadence['monthly']) {
            return $cadence['expired']; // monthly: not enforceable until the plan lapses
        }

        return true;
    }

    /**
     * The owner's latest paid-subscription cadence + expiry, guarded so it returns
     * null (→ standalone behaviour) wherever the legacy subscription tables aren't
     * present (e.g. the isolated test sandbox).
     *
     * @return array{monthly:bool, expired:bool}|null
     */
    private function planCadence(int $ownerUserId): ?array
    {
        try {
            $p = \App\Models\OwnerPackage::query()
                ->leftJoin('subscription_orders', 'subscription_orders.id', '=', 'owner_packages.order_id')
                ->where('owner_packages.user_id', $ownerUserId)
                ->where('owner_packages.pricing_model', 'subscription')
                ->orderByDesc('owner_packages.id')
                ->select('subscription_orders.duration_type', 'owner_packages.end_date')
                ->first();

            if (! $p) {
                return null;
            }

            return [
                'monthly' => (int) ($p->duration_type ?? 0) === PACKAGE_DURATION_TYPE_MONTHLY,
                'expired' => empty($p->end_date) || Carbon::parse($p->end_date)->lt(Carbon::now()),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
