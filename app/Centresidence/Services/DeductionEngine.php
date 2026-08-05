<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Support\Money;
use Illuminate\Support\Collection;

/**
 * Deduction Engine (handbook §9.6) — given a rent collection, decides how funds
 * split across beneficiaries in strict priority order, all within a single
 * GLOBAL ceiling (max_total_rent_deduction_percentage) so the owner always
 * keeps a predictable share of rent:
 *
 *   0. Global ceiling — the total deducted across all streams can never exceed
 *      this share of the rent; each stream below draws from within it.
 *   1. Centresidence commission fallback (overdue metered) — capped at 50% of
 *      this rent transaction (and the global ceiling).
 *   2. Active facility repayments, oldest first — each capped at its
 *      deduction_percentage of rent and at its own outstanding; the total
 *      capped at the most restrictive max_rent_deduction_percentage AND the
 *      remaining global budget.
 *   3. Remainder → owner net rent.
 *
 * This is a PURE calculation: it reads balances and returns a plan. Applying
 * the plan (writing transactions, updating balances, moving money) is the
 * RentSettlementService's job.
 */
class DeductionEngine
{
    /**
     * @param  Collection<CentresidenceCommissionInvoice>  $fallbackInvoices  active-fallback invoices for the property
     * @param  Collection<FinanceFacility>  $facilities  active facilities for the property (any order; sorted here)
     * @param  array<int,Money>  $cycleCaps  facility_id => remaining cap for this settlement cycle.
     *   When a facility id is present, its deduction is additionally capped at the
     *   remaining monthly target (so collection pauses once the month's target is
     *   met). Accelerated facilities are simply absent from this map (no cap).
     * @param  Collection<OwnerInfrastructureInvoice>  $infraInvoices  unpaid infra invoices (transaction owners)
     * @return array{rent:Money, fallback:Money, infrastructure:Money, infra_plans:array, facilities:array, total_deducted:Money, owner_net:Money}
     */
    public function plan(Money $rent, Collection $fallbackInvoices, Collection $infraInvoices, Collection $facilities, array $cycleCaps = []): array
    {
        $running = Money::zero();

        // ── 0. Global ceiling on TOTAL deductions from this rent ────────────
        // Every stream below draws from within this budget, so the owner always
        // keeps at least (100 − cap)% of their rent however the streams stack.
        // An owner may have CONSENTED to a higher personal cap on a facility (to
        // keep the agreed term); use it, but never above the hard consent max.
        $defaultPct = (float) config('centresidence.billing.max_total_rent_deduction_percentage', 60);
        $consentMax = (float) config('centresidence.billing.max_consented_rent_deduction_percentage', 90);
        $consented = (float) ($facilities->max('consented_deduction_cap') ?? 0);
        $maxTotalPct = (string) min($consentMax, max($defaultPct, $consented));
        $globalBudget = $rent->percentage($maxTotalPct);

        // ── 1. Commission fallback (cap: 50% of rent, within the global ceiling)
        $fallbackCapPct = (string) config('centresidence.billing.fallback_rent_cap_percentage', 50);
        $fallbackCap = $rent->percentage($fallbackCapPct);
        $fallbackOutstanding = $fallbackInvoices->reduce(
            fn (Money $c, $inv) => $c->plus($inv->meteredOutstanding()),
            Money::zero()
        );
        $fallbackTake = $fallbackCap->cappedAt($fallbackOutstanding)->cappedAt($globalBudget);
        $running = $running->plus($fallbackTake);
        $remainingGlobal = $globalBudget->minus($fallbackTake);

        // ── 1b. Infrastructure cost recovery (fixed monthly cost; recover what's
        // owed within the remaining global budget, oldest invoice first).
        $infraPlans = [];
        $infraBudget = $remainingGlobal;
        foreach ($infraInvoices->sortBy('billing_month')->values() as $invoice) {
            if (! $infraBudget->isPositive()) {
                break;
            }
            $take = $infraBudget->cappedAt($invoice->outstanding());
            if (! $take->isPositive()) {
                continue;
            }
            $infraPlans[] = ['invoice' => $invoice, 'amount' => $take];
            $infraBudget = $infraBudget->minus($take);
            $running = $running->plus($take);
        }
        $infraTake = $remainingGlobal->minus($infraBudget);
        $remainingGlobal = $infraBudget;

        // ── 2. Active facility repayments (oldest first; own cap, within global)
        $globalCapPct = $this->mostRestrictiveCap($facilities);
        $facilityBudget = $rent->percentage($globalCapPct)->cappedAt($remainingGlobal);

        $facilityPlans = [];
        foreach ($facilities->sortBy('created_at')->values() as $facility) {
            if (! $facilityBudget->isPositive()) {
                break;
            }

            $intended = $rent->percentage((string) $facility->deduction_percentage)
                ->cappedAt($facility->outstandingTotal())
                ->cappedAt($facilityBudget);

            // Per-cycle cap: pause once the month's target is met (unless the
            // facility is on accelerated repayment, in which case it's absent).
            if (isset($cycleCaps[$facility->id])) {
                $intended = $intended->cappedAt($cycleCaps[$facility->id]);
            }

            if (! $intended->isPositive()) {
                continue;
            }

            // Apply within the facility: penalty → interest → principal.
            $penalty = $intended->cappedAt(Money::fromDecimal($facility->outstanding_penalty));
            $afterPenalty = $intended->minus($penalty);
            $interest = $afterPenalty->cappedAt(Money::fromDecimal($facility->outstanding_interest));
            $afterInterest = $afterPenalty->minus($interest);
            $principal = $afterInterest->cappedAt(Money::fromDecimal($facility->outstanding_principal));

            $amount = $penalty->plus($interest)->plus($principal);
            if (! $amount->isPositive()) {
                continue;
            }

            $facilityPlans[] = [
                'facility' => $facility,
                'amount' => $amount,
                'penalty' => $penalty,
                'interest' => $interest,
                'principal' => $principal,
            ];

            $facilityBudget = $facilityBudget->minus($amount);
            $running = $running->plus($amount);
        }

        return [
            'rent' => $rent,
            'fallback' => $fallbackTake,
            'infrastructure' => $infraTake,
            'infra_plans' => $infraPlans,
            'facilities' => $facilityPlans,
            'total_deducted' => $running,
            'owner_net' => $rent->minus($running),
        ];
    }

    /**
     * The most restrictive max_rent_deduction_percentage among active facilities
     * (handbook §9.6.1, step 2). Defaults to 100 when none constrain.
     */
    private function mostRestrictiveCap(Collection $facilities): string
    {
        $caps = $facilities
            ->map(fn ($f) => (float) $f->deduction_percentage)
            ->filter(fn ($v) => $v > 0);

        // The cap is the sum the facilities would request, but never more than
        // the tightest partner-imposed ceiling. We use the max partner ceiling
        // field as the global cap; fall back to the sum of deduction %s.
        $partnerCaps = $facilities
            ->map(fn ($f) => (float) optional($f->application?->partnerModule)->max_rent_deduction_percentage)
            ->filter(fn ($v) => $v > 0);

        if ($partnerCaps->isNotEmpty()) {
            return (string) $partnerCaps->min();
        }

        return $caps->isNotEmpty() ? (string) min(100, $caps->sum()) : '100';
    }
}
