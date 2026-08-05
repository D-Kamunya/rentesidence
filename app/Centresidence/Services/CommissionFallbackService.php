<?php

namespace App\Centresidence\Services;

use App\Centresidence\Events\CommissionFallbackActivated;
use App\Centresidence\Events\CommissionFallbackCleared;
use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;

/**
 * Dual-pathway fallback (handbook §8.1 fallback / §9.6.1).
 *
 * Tenant-continuity is the prime directive: ONLY the metered, fallback-eligible
 * portion of an overdue commission invoice is recovered from token revenue, and
 * the deduction comes out of OWNER REVENUE — never the tenant's units. The
 * non-metered portion (locks, parking) is NEVER token-recovered; it stays on
 * the invoice for standard dunning.
 */
class CommissionFallbackService
{
    /**
     * Activate token-deduction fallback on commission invoices whose metered
     * balance is overdue past the grace period. Returns the number activated.
     */
    public function activateOverdue(?Carbon $asOf = null): int
    {
        $asOf = ($asOf ?? Carbon::now())->copy();
        $graceDays = (int) config('centresidence.billing.commission_grace_days', 7);
        $threshold = $asOf->copy()->subDays($graceDays);

        $invoices = CentresidenceCommissionInvoice::query()
            ->whereIn('status', [
                CentresidenceCommissionInvoice::STATUS_PENDING,
                CentresidenceCommissionInvoice::STATUS_OVERDUE,
                CentresidenceCommissionInvoice::STATUS_PARTIALLY_PAID,
            ])
            ->where('fallback_deduction_active', false)
            ->whereNull('fallback_metered_cleared_at')
            ->whereColumn('metered_paid_total', '<', 'metered_commission_total')
            ->whereDate('billing_month', '<', $threshold->toDateString())
            ->get();

        $standing = app(OwnerBillingStandingService::class);

        $count = 0;
        foreach ($invoices as $invoice) {
            // Guard: nothing metered to recover → never activate (protects the
            // non-metered-only case).
            if (! $invoice->meteredOutstanding()->isPositive()) {
                continue;
            }

            // Cadence gate (option A): don't recover an active MONTHLY owner's
            // token revenue for infra that isn't due until their plan renewal —
            // the merge bundles it then. Yearly / plan-less owners follow grace.
            if (! $standing->mayEnforceInfra((int) $invoice->owner_id)) {
                continue;
            }

            $invoice->forceFill([
                'fallback_deduction_active' => true,
                'fallback_deduction_started_at' => $asOf,
                'status' => CentresidenceCommissionInvoice::STATUS_OVERDUE,
            ])->save();

            CommissionFallbackActivated::dispatch($invoice);
            $count++;
        }

        return $count;
    }

    /**
     * Intercept a share of a token purchase's owner revenue toward this owner's
     * overdue metered commission for the property. Returns the amount deducted
     * (from owner revenue, capped at the outstanding metered balance).
     */
    public function applyToOwnerRevenue(int $ownerId, int $propertyId, Money $ownerRevenue): Money
    {
        $pct = (string) config('centresidence.billing.fallback_token_intercept_percentage', 100);
        $interceptCap = $ownerRevenue->percentage($pct);

        if (! $interceptCap->isPositive()) {
            return Money::zero();
        }

        // Lock the candidate invoice rows so concurrent token purchases cannot
        // both read the same outstanding metered balance and over-recover.
        // Intended to run inside the TokenEngine purchase transaction. (no-op on
        // sqlite, pins rows on MySQL.)
        $invoices = CentresidenceCommissionInvoice::query()
            ->where('owner_id', $ownerId)
            ->where('property_id', $propertyId)
            ->where('fallback_deduction_active', true)
            ->whereNull('fallback_metered_cleared_at')
            ->orderBy('billing_month')
            ->lockForUpdate()
            ->get();

        $remaining = $interceptCap;
        $deducted = Money::zero();

        foreach ($invoices as $invoice) {
            $outstanding = $invoice->meteredOutstanding();
            if (! $outstanding->isPositive()) {
                continue;
            }

            $take = $remaining->cappedAt($outstanding);
            if (! $take->isPositive()) {
                break;
            }

            $newPaid = Money::fromDecimal($invoice->metered_paid_total ?? '0')->plus($take);
            $invoice->metered_paid_total = $newPaid->toDecimal();

            $cleared = ! $invoice->meteredOutstanding()->isPositive();
            if ($cleared) {
                $invoice->fallback_deduction_active = false;
                $invoice->fallback_metered_cleared_at = Carbon::now();
                // Metered cleared; non-metered (if any) remains outstanding.
                $invoice->status = $invoice->hasNonMetered()
                    ? CentresidenceCommissionInvoice::STATUS_PARTIALLY_PAID
                    : CentresidenceCommissionInvoice::STATUS_PAID;
                if (! $invoice->hasNonMetered()) {
                    $invoice->paid_at = Carbon::now();
                }
            }
            $invoice->save();

            if ($cleared) {
                CommissionFallbackCleared::dispatch($invoice);
            }

            $deducted = $deducted->plus($take);
            $remaining = $remaining->minus($take);

            if (! $remaining->isPositive()) {
                break;
            }
        }

        return $deducted;
    }
}
