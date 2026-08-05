<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;

/**
 * Interest accrual + early-settlement maths, differentiated by the facility's
 * interest type (handbook §9.4; partner sets interest_rate_type):
 *
 *   - reducing_balance → interest ACCRUES as each scheduled period matures.
 *     Only matured interest is owed, so repaying principal early stops future
 *     interest from ever accruing — the borrower genuinely saves.
 *   - flat → the whole interest is pre-booked at creation and owed regardless
 *     of speed (early repayment saves nothing unless the partner rebates).
 *
 * "Matured" interest = the sum of interest_due for periods whose due_date has
 * passed. Outstanding interest = matured − already paid.
 */
class FacilityInterestService
{
    public function isFlat(FinanceFacility $facility): bool
    {
        return $this->method($facility) === 'flat';
    }

    /** Interest considered DUE as of $asOf (flat = all of it; reducing = matured periods). */
    public function matureInterestDue(FinanceFacility $facility, ?Carbon $asOf = null): Money
    {
        $asOf = $asOf ?? Carbon::now();

        if ($this->isFlat($facility)) {
            // All scheduled interest is due immediately.
            return $facility->schedules()->get()->reduce(
                fn (Money $c, $row) => $c->plus(Money::fromDecimal($row->interest_due)),
                Money::zero()
            );
        }

        // Reducing balance: only interest of periods that have matured.
        return $facility->schedules()
            ->whereDate('due_date', '<=', $asOf->toDateString())
            ->get()
            ->reduce(fn (Money $c, $row) => $c->plus(Money::fromDecimal($row->interest_due)), Money::zero());
    }

    public function interestPaid(FinanceFacility $facility): Money
    {
        return $facility->schedules()->get()->reduce(
            fn (Money $c, $row) => $c->plus(Money::fromDecimal($row->interest_paid)),
            Money::zero()
        );
    }

    /** Outstanding (owed-but-unpaid) interest as of $asOf. */
    public function outstandingInterest(FinanceFacility $facility, ?Carbon $asOf = null): Money
    {
        $owed = $this->matureInterestDue($facility, $asOf)->minus($this->interestPaid($facility));

        return $owed->isPositive() ? $owed : Money::zero();
    }

    /** Recompute and persist the facility's outstanding_interest. */
    public function syncOutstandingInterest(FinanceFacility $facility, ?Carbon $asOf = null): void
    {
        $facility->outstanding_interest = $this->outstandingInterest($facility, $asOf)->toDecimal();
        $facility->save();
    }

    /**
     * Early-settlement payoff as of $asOf:
     *   outstanding_principal + interest accrued-to-date + outstanding penalty
     *   + early-settlement fee (partner's early_repayment_penalty_percentage on
     *   the principal). Future (unearned) interest is excluded for reducing
     *   balance — that is the saving.
     *
     * @return array{principal:Money, interest:Money, penalty:Money, fee:Money, total:Money}
     */
    public function earlySettlementQuote(FinanceFacility $facility, ?Carbon $asOf = null): array
    {
        $principal = Money::fromDecimal($facility->outstanding_principal);
        $interest = $this->outstandingInterest($facility, $asOf);
        $penalty = Money::fromDecimal($facility->outstanding_penalty);

        $feePct = (string) optional($facility->application?->partnerModule)->early_repayment_penalty_percentage ?: '0';
        $fee = $principal->percentage($feePct);

        $total = $principal->plus($interest)->plus($penalty)->plus($fee);

        return compact('principal', 'interest', 'penalty', 'fee', 'total');
    }

    private function method(FinanceFacility $facility): string
    {
        // Prefer the originating partner-product interest type; fall back to the
        // facility's calculation method.
        $type = optional($facility->application?->partnerModule)->interest_rate_type;

        return $type ?: ($facility->interest_calculation_method === 'flat_upfront' ? 'flat' : 'reducing_balance');
    }
}
