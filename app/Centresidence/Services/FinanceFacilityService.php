<?php

namespace App\Centresidence\Services;

use App\Centresidence\Events\FacilityCreated;
use App\Centresidence\Events\FacilityDisbursed;
use App\Centresidence\Events\FacilitySettledEarly;
use App\Centresidence\Models\FacilityTransaction;
use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Models\RepaymentSchedule;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Finance Facility Engine (handbook §9.4). Turns an approved application into a
 * live facility with a generated amortisation schedule, and handles
 * disbursement (which flags the platform fee for settlement).
 *
 * Repayment *collection* (applying rent deductions to the schedule) belongs to
 * the Settlement Engine (WP8); this engine sets up the facility and its plan.
 */
class FinanceFacilityService
{
    public function __construct(
        private RepaymentScheduleBuilder $scheduleBuilder,
        private FacilityInterestService $interest
    ) {
    }

    /**
     * Create a facility + repayment schedule from an approved application.
     * The owner repays the full financed amount (base + platform fee); the
     * platform fee is Centresidence's cut, settled separately at disbursement.
     */
    public function createFromApplication(FinanceApplication $application): FinanceFacility
    {
        $partnerModule = $application->partnerModule;

        // Facility amount = approved, else the FINANCED portion (total project
        // cost − owner down-payment). financed_amount of 0 means "not set"
        // (legacy/direct rows) → fall back to the full requested amount.
        $financed = (float) $application->financed_amount > 0
            ? $application->financed_amount
            : $application->requested_amount;
        $facilityAmount = Money::fromDecimal($application->approved_amount ?? $financed);
        $platformFee = Money::fromDecimal($application->platform_fee_amount);
        $months = (int) $application->repayment_months;
        $annualRate = (string) ($application->interest_rate_snapshot ?? $partnerModule->interest_rate);
        $method = $partnerModule->interest_rate_type;

        $schedule = $this->scheduleBuilder->build($facilityAmount, $annualRate, $months, $method);
        $monthly = $schedule['monthly'];
        $totalRepayable = $schedule['total_repayable'];
        $totalInterest = $totalRepayable->minus($facilityAmount);

        // Interest treatment by type: flat pre-books the whole interest as owed;
        // reducing-balance accrues it as periods mature (starts at zero), so
        // early repayment genuinely saves the borrower interest.
        $isFlat = $method === 'flat';
        $initialOutstandingInterest = $isFlat ? $totalInterest : Money::zero();

        return DB::transaction(function () use (
            $application, $partnerModule, $facilityAmount, $platformFee, $months,
            $annualRate, $monthly, $totalRepayable, $initialOutstandingInterest, $schedule
        ) {
            $facility = FinanceFacility::create([
                'finance_application_id' => $application->id,
                'finance_partner_id' => $application->finance_partner_id,
                'owner_id' => $application->owner_id,
                'property_id' => $application->property_id,
                'module_id' => $application->module_id,
                'disbursed_amount' => $facilityAmount->toDecimal(),
                'owner_contribution' => $application->owner_contribution ?? 0,
                'principal_amount' => $facilityAmount->toDecimal(),
                'platform_fee_amount' => $platformFee->toDecimal(),
                'interest_rate' => $annualRate,
                'interest_calculation_method' => $partnerModule->interest_calculation_method,
                'penalty_rate' => $partnerModule->penalty_rate,
                'total_repayable' => $totalRepayable->toDecimal(),
                'outstanding_principal' => $facilityAmount->toDecimal(),
                'outstanding_interest' => $initialOutstandingInterest->toDecimal(),
                'monthly_target' => $monthly->toDecimal(),
                'deduction_percentage' => $application->repayment_percentage ?? $partnerModule->max_rent_deduction_percentage,
                'consented_deduction_cap' => $application->consented_deduction_cap,
                'repayment_months' => $months,
                'first_repayment_date' => Carbon::now()->addMonth()->toDateString(),
                'maturity_date' => Carbon::now()->addMonths($months)->toDateString(),
                'grace_period_days' => $partnerModule->grace_period_days,
                'default_threshold_days' => $partnerModule->default_threshold_days,
                'status' => FinanceFacility::STATUS_ACTIVE,
            ]);

            $facility->forceFill([
                'facility_number' => 'FAC-' . now()->year . '-' . str_pad((string) $facility->id, 5, '0', STR_PAD_LEFT),
            ])->save();

            foreach ($schedule['rows'] as $row) {
                $facility->schedules()->create($row);
            }

            FacilityCreated::dispatch($facility);

            return $facility;
        });
    }

    /**
     * Settle a facility early (handbook §9.2.2 early_repayment). Pays off the
     * outstanding principal + interest accrued to date + outstanding penalty +
     * the partner's early-settlement fee, and completes the facility. For
     * reducing-balance facilities, future (unearned) interest is excluded — the
     * borrower's saving. Honours the partner's early_repayment_allowed flag.
     *
     * @return array{principal:string, interest:string, penalty:string, fee:string, total:string}
     */
    public function settleEarly(FinanceFacility $facility, array $opts = []): array
    {
        $allowed = optional($facility->application?->partnerModule)->early_repayment_allowed;
        if ($allowed === false) {
            throw new \RuntimeException("Early repayment is not allowed on facility {$facility->id}.");
        }

        $this->interest->syncOutstandingInterest($facility);
        $facility->refresh();
        $quote = $this->interest->earlySettlementQuote($facility);
        $source = $opts['source'] ?? 'owner_payment';

        return DB::transaction(function () use ($facility, $quote, $source) {
            $components = [
                FacilityTransaction::TYPE_REPAYMENT_PENALTY => $quote['penalty'],
                FacilityTransaction::TYPE_REPAYMENT_INTEREST => $quote['interest'],
                FacilityTransaction::TYPE_REPAYMENT_PRINCIPAL => $quote['principal'],
                'fee' => $quote['fee'],
            ];
            foreach ($components as $type => $amount) {
                if (! $amount->isPositive()) {
                    continue;
                }
                FacilityTransaction::create([
                    'finance_facility_id' => $facility->id,
                    'transaction_type' => $type,
                    'amount' => $amount->toDecimal(),
                    'direction' => 'debit',
                    'source' => $source,
                    'reference' => 'early_settlement',
                    'created_at' => Carbon::now(),
                ]);
            }

            $facility->forceFill([
                'outstanding_principal' => '0.00',
                'outstanding_interest' => '0.00',
                'outstanding_penalty' => '0.00',
                'status' => FinanceFacility::STATUS_COMPLETED,
                'completed_at' => Carbon::now(),
            ])->save();

            $facility->schedules()->whereIn('status', ['pending', 'partial', 'overdue'])
                ->update(['status' => 'paid']);

            FacilitySettledEarly::dispatch($facility);

            return [
                'principal' => $quote['principal']->toDecimal(),
                'interest' => $quote['interest']->toDecimal(),
                'penalty' => $quote['penalty']->toDecimal(),
                'fee' => $quote['fee']->toDecimal(),
                'total' => $quote['total']->toDecimal(),
            ];
        });
    }

    /** Owner opt-in/out of accelerated repayment (lifts the per-cycle cap). */
    public function setAccelerated(FinanceFacility $facility, bool $accelerated): FinanceFacility
    {
        $facility->forceFill(['accelerated_repayment' => $accelerated])->save();

        return $facility;
    }

    /**
     * Release funds: stamp the disbursement, record a disbursement transaction,
     * and flag the platform fee for settlement (handbook §9.7 step 7).
     */
    public function disburse(FinanceFacility $facility, ?string $reference = null): FinanceFacility
    {
        DB::transaction(function () use ($facility, $reference) {
            $facility->forceFill([
                'disbursement_date' => Carbon::now()->toDateString(),
                'status' => FinanceFacility::STATUS_ACTIVE,
            ])->save();

            FacilityTransaction::create([
                'finance_facility_id' => $facility->id,
                'transaction_type' => FacilityTransaction::TYPE_DISBURSEMENT,
                'amount' => $facility->disbursed_amount,
                'direction' => 'credit',
                'source' => 'system',
                'reference' => $reference,
                'created_at' => Carbon::now(),
            ]);
        });

        // Dispatch AFTER the transaction commits: the down-payment listener may
        // perform an M-Pesa STK HTTP call (mpesa driver), which must not run
        // inside an open DB transaction holding row locks during network I/O.
        FacilityDisbursed::dispatch($facility);

        return $facility;
    }
}
