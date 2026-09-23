<?php

namespace App\Centresidence\Services;

use App\Centresidence\Events\FacilityRestructured;
use App\Centresidence\Models\FacilityDefault;
use App\Centresidence\Models\FacilityRestructure;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Restructures a defaulted facility on new terms (handbook §9.5.5): rolls the
 * total outstanding into a fresh principal, regenerates the repayment schedule,
 * updates the facility, and resolves the default.
 */
class FacilityRestructureService
{
    public function __construct(private RepaymentScheduleBuilder $scheduleBuilder)
    {
    }

    /**
     * @param  array{new_interest_rate:float, new_repayment_months:int,
     *   new_deduction_percentage?:float, restructure_fee?:float, method?:string}  $terms
     */
    public function restructure(FacilityDefault $default, array $terms): FacilityRestructure
    {
        $facility = $default->facility;

        // Roll all outstanding (principal + interest + penalty) + restructure fee
        // into the new principal.
        $newPrincipal = $facility->outstandingTotal()
            ->plus(Money::fromDecimal((string) ($terms['restructure_fee'] ?? 0)));

        $rate = (string) $terms['new_interest_rate'];
        $months = (int) $terms['new_repayment_months'];
        $method = $terms['method'] ?? 'reducing_balance';

        $schedule = $this->scheduleBuilder->build($newPrincipal, $rate, $months, $method);
        $monthly = $schedule['monthly'];
        $totalRepayable = $schedule['total_repayable'];

        return DB::transaction(function () use ($facility, $default, $terms, $newPrincipal, $rate, $months, $monthly, $totalRepayable, $schedule) {
            // Replace the unpaid schedule with the new plan.
            $facility->schedules()->whereIn('status', ['pending', 'partial', 'overdue'])->delete();
            foreach ($schedule['rows'] as $row) {
                $facility->schedules()->create($row);
            }

            $facility->forceFill([
                'interest_rate' => $rate,
                'repayment_months' => $months,
                'monthly_target' => $monthly->toDecimal(),
                'deduction_percentage' => $terms['new_deduction_percentage'] ?? $facility->deduction_percentage,
                'total_repayable' => $totalRepayable->toDecimal(),
                'outstanding_principal' => $newPrincipal->toDecimal(),
                'outstanding_interest' => $totalRepayable->minus($newPrincipal)->toDecimal(),
                'outstanding_penalty' => '0.00',
                'days_past_due' => 0,
                'maturity_date' => Carbon::now()->addMonths($months)->toDateString(),
                'status' => \App\Centresidence\Models\FinanceFacility::STATUS_ACTIVE,
            ])->save();

            $restructure = FacilityRestructure::create([
                'finance_facility_id' => $facility->id,
                'facility_default_id' => $default->id,
                'new_interest_rate' => $rate,
                'new_repayment_months' => $months,
                'new_monthly_target' => $monthly->toDecimal(),
                'new_deduction_percentage' => $terms['new_deduction_percentage'] ?? $facility->deduction_percentage,
                'new_maturity_date' => Carbon::now()->addMonths($months)->toDateString(),
                'restructure_fee' => $terms['restructure_fee'] ?? 0,
                'approved_by_partner' => $terms['approved_by_partner'] ?? true,
                'approved_by_owner' => $terms['approved_by_owner'] ?? true,
                'effective_date' => Carbon::now()->toDateString(),
                'created_at' => Carbon::now(),
            ]);

            $default->forceFill([
                'resolved_at' => Carbon::now(),
                'resolution_type' => FacilityDefault::RESOLUTION_RESTRUCTURED,
            ])->save();

            FacilityRestructured::dispatch($facility);

            return $restructure;
        });
    }
}
