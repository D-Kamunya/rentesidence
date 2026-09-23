<?php

namespace App\Centresidence\Services;

use App\Centresidence\Events\FacilityDefaulted;
use App\Centresidence\Events\RepaymentOverdue;
use App\Centresidence\Models\FacilityDefault;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Models\RepaymentSchedule;
use App\Centresidence\Services\FacilityInterestService;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Collections & default management (handbook §9.5.5 / §9.6.3). On each run it:
 *   - marks past-due schedule rows overdue and accrues penalty interest,
 *   - tracks days_past_due on the facility,
 *   - fires RepaymentOverdue once past the grace period,
 *   - escalates to default once past the default threshold (creating a
 *     facility_default snapshot and flipping the facility to defaulted).
 *
 * Penalty is recomputed from scratch each run (set, not incremented) so repeated
 * runs are idempotent.
 */
class FacilityCollectionsService
{
    public function __construct(private FacilityInterestService $interest)
    {
    }

    /**
     * @return array{overdue:int, defaulted:int}
     */
    public function run(?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? Carbon::now())->copy();
        $overdue = 0;
        $defaulted = 0;

        $facilities = FinanceFacility::query()
            ->where('status', FinanceFacility::STATUS_ACTIVE)
            ->get();

        foreach ($facilities as $facility) {
            // Accrue interest as periods mature (reducing-balance) before
            // assessing overdue/default.
            $this->interest->syncOutstandingInterest($facility, $asOf);

            $result = $this->assess($facility, $asOf);
            if ($result['overdue']) {
                $overdue++;
            }
            if ($result['defaulted']) {
                $defaulted++;
            }
        }

        return ['overdue' => $overdue, 'defaulted' => $defaulted];
    }

    private function assess(FinanceFacility $facility, Carbon $asOf): array
    {
        $pastDueRows = $facility->schedules()
            ->whereIn('status', [RepaymentSchedule::STATUS_PENDING, RepaymentSchedule::STATUS_PARTIAL, RepaymentSchedule::STATUS_OVERDUE])
            ->whereDate('due_date', '<', $asOf->toDateString())
            ->orderBy('due_date')
            ->get();

        if ($pastDueRows->isEmpty()) {
            return ['overdue' => false, 'defaulted' => false];
        }

        $earliestDue = Carbon::parse($pastDueRows->first()->due_date);
        $daysPastDue = max(0, $earliestDue->diffInDays($asOf, false));

        return DB::transaction(function () use ($facility, $asOf, $pastDueRows, $daysPastDue) {
            // Mark rows overdue + recompute penalty across all past-due rows.
            $penalty = Money::zero();
            $dailyRate = (float) $facility->penalty_rate / 100 / 365;

            foreach ($pastDueRows as $row) {
                $rowDays = max(0, Carbon::parse($row->due_date)->diffInDays($asOf, false));
                $shortfall = Money::fromDecimal($row->total_due)->minus(Money::fromDecimal($row->total_paid));
                if ($shortfall->isPositive()) {
                    $accrued = Money::fromDecimal(number_format($shortfall->toFloat() * $dailyRate * $rowDays, 2, '.', ''));
                    $penalty = $penalty->plus($accrued);
                }
                $row->forceFill(['status' => RepaymentSchedule::STATUS_OVERDUE, 'days_overdue' => $rowDays])->save();
            }

            $facility->outstanding_penalty = $penalty->toDecimal();
            $facility->days_past_due = $daysPastDue;
            $facility->save();

            $isOverdue = $daysPastDue > (int) $facility->grace_period_days;
            if ($isOverdue) {
                RepaymentOverdue::dispatch($facility, $daysPastDue);
            }

            $defaulted = false;
            $threshold = (int) $facility->default_threshold_days;
            if ($threshold > 0 && $daysPastDue > $threshold) {
                $defaulted = $this->escalateToDefault($facility, $asOf, $daysPastDue);
            }

            return ['overdue' => $isOverdue, 'defaulted' => $defaulted];
        });
    }

    private function escalateToDefault(FinanceFacility $facility, Carbon $asOf, int $daysPastDue): bool
    {
        // Idempotent: one unresolved default per facility.
        $exists = FacilityDefault::where('finance_facility_id', $facility->id)->whereNull('resolved_at')->exists();
        if ($exists) {
            return false;
        }

        $default = FacilityDefault::create([
            'finance_facility_id' => $facility->id,
            'defaulted_at' => $asOf,
            'days_past_due_at_default' => $daysPastDue,
            'outstanding_principal_at_default' => $facility->outstanding_principal,
            'outstanding_interest_at_default' => $facility->outstanding_interest,
            'outstanding_penalty_at_default' => $facility->outstanding_penalty,
            'total_outstanding_at_default' => $facility->outstandingTotal()->toDecimal(),
            'default_reason' => 'payment_failure',
            'collections_status' => FacilityDefault::COLLECTIONS_INTERNAL,
        ]);

        $facility->forceFill([
            'status' => FinanceFacility::STATUS_DEFAULTED,
            'defaulted_at' => $asOf,
        ])->save();

        FacilityDefaulted::dispatch($default);

        return true;
    }
}
