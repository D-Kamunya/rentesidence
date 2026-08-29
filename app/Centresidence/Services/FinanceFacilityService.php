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

        // Origination fee — one-time, % of principal, booked NOW as owed by the partner;
        // only COLLECTED once the facility is disbursed (netted from remittances — see
        // PartnerRemittanceService). Never demands payment before the partner sees value.
        $partner = \App\Centresidence\Models\FinancePartner::find($application->finance_partner_id);
        $originationRate = $partner
            ? app(\App\Centresidence\Services\PartnerFeeService::class)->originationRate($partner)
            : (float) config('centresidence.partner_fees.origination_percentage', 2.0);
        $originationFee = round($facilityAmount->toDecimal() * $originationRate / 100, 2);

        return DB::transaction(function () use (
            $application, $partnerModule, $facilityAmount, $platformFee, $months,
            $annualRate, $monthly, $totalRepayable, $initialOutstandingInterest, $schedule, $originationFee
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
                'origination_fee_amount' => $originationFee,
                'origination_fee_collected' => 0,
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
                // Funds are NOT released yet — approval creates the facility, disbursement is
                // a separate confirmed step. Rent only repays once this reaches DISBURSE_DONE.
                'disbursement_status' => FinanceFacility::DISBURSE_AWAITING,
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
    /**
     * Direct early settlement (tests + admin manual lever) — completes immediately
     * and returns the payoff breakdown. The OWNER-facing path goes through
     * initiateEarlySettlement() so a real payment is confirmed before completing.
     *
     * @return array{principal:string, interest:string, penalty:string, fee:string, total:string}
     */
    public function settleEarly(FinanceFacility $facility, array $opts = []): array
    {
        $allowed = optional($facility->application?->partnerModule)->early_repayment_allowed;
        if ($allowed === false) {
            throw new \RuntimeException("Early repayment is not allowed on facility {$facility->id}.");
        }
        $facility->forceFill([
            'early_settlement_status'  => FinanceFacility::EARLY_PENDING,
            'early_settlement_channel' => $opts['channel'] ?? 'manual',
        ])->save();

        return $this->finalizeEarlySettlement($facility->fresh(), $opts['source'] ?? 'owner_payment');
    }

    /**
     * Begin early settlement. The payoff is only COMPLETED once the money is
     * actually confirmed — never a free status flip. Channels:
     *   'mpesa'  — STK the owner for the payoff (log driver settles now in dev; the
     *              real driver waits for the callback);
     *   'manual' — the owner paid the partner out-of-system (bank), the partner
     *              confirms receipt to complete it.
     * Honours the partner's early_repayment_allowed flag.
     *
     * @return array{status:string, total:string}  status = settled | pending
     */
    public function initiateEarlySettlement(FinanceFacility $facility, string $channel, ?string $reference = null): array
    {
        $allowed = optional($facility->application?->partnerModule)->early_repayment_allowed;
        if ($allowed === false) {
            throw new \RuntimeException("Early repayment is not allowed on facility {$facility->id}.");
        }
        if ($facility->status === FinanceFacility::STATUS_COMPLETED
            || $facility->early_settlement_status === FinanceFacility::EARLY_SETTLED) {
            throw new \RuntimeException("Facility {$facility->id} is already settled.");
        }

        $this->interest->syncOutstandingInterest($facility);
        $facility->refresh();
        $quote = $this->interest->earlySettlementQuote($facility);

        $facility->forceFill([
            'early_settlement_status'    => FinanceFacility::EARLY_PENDING,
            'early_settlement_channel'   => $channel,
            'early_settlement_reference' => $reference,
            'early_settlement_amount'    => $quote['total']->toDecimal(),
        ])->save();

        if ($channel === 'mpesa') {
            // Dev (log driver): treat as paid immediately; production waits for the STK callback.
            if (config('centresidence.collections.driver', 'log') !== 'mpesa') {
                $this->finalizeEarlySettlement($facility->fresh(), 'owner_payment');
                return ['status' => 'settled', 'total' => $quote['total']->toDecimal()];
            }
            $this->pushSettlementStk($facility, $quote['total']);
            return ['status' => 'pending', 'total' => $quote['total']->toDecimal()];
        }

        // Manual (bank): stays pending until the partner confirms receipt.
        return ['status' => 'pending', 'total' => $quote['total']->toDecimal()];
    }

    /** Confirm the payoff was received (STK callback, or the partner for a manual payment) → complete. */
    public function confirmEarlySettlement(FinanceFacility $facility, ?string $reference = null): array
    {
        if ($reference) {
            $facility->forceFill(['early_settlement_reference' => $reference])->save();
        }
        // The money is the owner's payoff in both channels (M-Pesa or bank); the
        // partner simply confirms receipt for the manual path.
        return $this->finalizeEarlySettlement($facility, 'owner_payment');
    }

    /** The actual completion: zero the balance, complete the facility, record the partner's payoff for remittance. */
    private function finalizeEarlySettlement(FinanceFacility $facility, string $source): array
    {
        $this->interest->syncOutstandingInterest($facility);
        $facility->refresh();
        $quote = $this->interest->earlySettlementQuote($facility);

        return DB::transaction(function () use ($facility, $quote, $source) {
            if ($facility->status === FinanceFacility::STATUS_COMPLETED) {
                return $this->quoteArray($quote); // idempotent
            }

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
                    'reference' => $facility->early_settlement_reference ?: 'early_settlement',
                    'created_at' => Carbon::now(),
                ]);
            }

            $facility->forceFill([
                'outstanding_principal' => '0.00',
                'outstanding_interest' => '0.00',
                'outstanding_penalty' => '0.00',
                'status' => FinanceFacility::STATUS_COMPLETED,
                'completed_at' => Carbon::now(),
                'early_settlement_status' => FinanceFacility::EARLY_SETTLED,
                'early_settlement_at' => Carbon::now(),
            ])->save();

            $facility->schedules()->whereIn('status', ['pending', 'partial', 'overdue'])
                ->update(['status' => 'paid']);

            // The partner is owed the full payoff → record it (split across the valid
            // repayment types) so the remittance run pays them. Remittance keys off
            // beneficiary + reconciliation_status, not the type.
            if ($facility->finance_partner_id) {
                $method = $facility->early_settlement_channel === 'mpesa' ? 'mobile_money' : 'bank_transfer';
                $penaltyPlusFee = Money::fromDecimal(bcadd($quote['penalty']->toDecimal(), $quote['fee']->toDecimal(), 2));
                foreach ([
                    'rent_deduction_principal' => $quote['principal'],
                    'rent_deduction_interest'  => $quote['interest'],
                    'rent_deduction_penalty'   => $penaltyPlusFee,
                ] as $type => $amt) {
                    if (! $amt->isPositive()) {
                        continue;
                    }
                    \App\Centresidence\Models\SettlementTransaction::create([
                        'finance_facility_id'   => $facility->id,
                        'transaction_type'      => $type,
                        'amount'                => $amt->toDecimal(),
                        'beneficiary_type'      => \App\Centresidence\Models\SettlementTransaction::BENEFICIARY_PARTNER,
                        'beneficiary_id'        => $facility->finance_partner_id,
                        'settlement_method'     => $method,
                        'settlement_reference'  => $facility->early_settlement_reference,
                        'reconciliation_status' => \App\Centresidence\Models\SettlementTransaction::RECON_PENDING,
                        'created_at'            => Carbon::now(),
                    ]);
                }
            }

            FacilitySettledEarly::dispatch($facility);

            return $this->quoteArray($quote);
        });
    }

    private function quoteArray(array $quote): array
    {
        return [
            'principal' => $quote['principal']->toDecimal(),
            'interest'  => $quote['interest']->toDecimal(),
            'penalty'   => $quote['penalty']->toDecimal(),
            'fee'       => $quote['fee']->toDecimal(),
            'total'     => $quote['total']->toDecimal(),
        ];
    }

    private function pushSettlementStk(FinanceFacility $facility, Money $amount): void
    {
        $phone = optional(\App\Models\User::find($facility->owner_id))->contact_number;
        $account = ($accountId = getOption('centresidence_rent_mpesa_account_id'))
            ? \App\Models\MpesaAccount::find($accountId) : null;

        if (! $phone || ! $account) {
            \Illuminate\Support\Facades\Log::warning('Early-settlement STK skipped: missing owner phone or collection account', ['facility_id' => $facility->id]);
            return;
        }

        $result = app(\App\Services\Payment\MpesaStkService::class)->push(
            $phone, (float) $amount->toDecimal(), $account,
            'Early settlement ' . $facility->facility_number,
            route('centresidence.settle.callback', ['facility' => $facility->id])
        );

        if (($result['success'] ?? false) && ! empty($result['reference'])) {
            $facility->forceFill(['early_settlement_reference' => $result['reference']])->save();
        }
    }

    /**
     * Owner opt-in/out of accelerated repayment (lifts the per-cycle cap).
     * Honours the partner's accelerated_repayment_allowed flag when ENABLING;
     * turning it back off is always permitted.
     */
    public function setAccelerated(FinanceFacility $facility, bool $accelerated): FinanceFacility
    {
        if ($accelerated) {
            $allowed = optional($facility->application?->partnerModule)->accelerated_repayment_allowed;
            if ($allowed === false) {
                throw new \RuntimeException("Accelerated repayment is not allowed on facility {$facility->id}.");
            }
        }

        $facility->forceFill(['accelerated_repayment' => $accelerated])->save();

        return $facility;
    }

    /**
     * Release funds: stamp the disbursement, record a disbursement transaction,
     * and flag the platform fee for settlement (handbook §9.7 step 7).
     */
    public function disburse(FinanceFacility $facility, ?string $reference = null, string $channel = 'manual'): FinanceFacility
    {
        if ($facility->isDisbursed()) {
            return $facility; // idempotent — already released
        }

        DB::transaction(function () use ($facility, $reference, $channel) {
            $facility->forceFill([
                'disbursement_status'    => FinanceFacility::DISBURSE_DONE,
                'disbursed_at'           => Carbon::now(),
                'disbursement_channel'   => $facility->disbursement_channel ?: $channel,
                'disbursement_reference' => $reference ?: $facility->disbursement_reference,
                'disbursement_date'      => Carbon::now()->toDateString(),
                'status'                 => FinanceFacility::STATUS_ACTIVE,
            ])->save();

            FacilityTransaction::create([
                'finance_facility_id' => $facility->id,
                'transaction_type' => FacilityTransaction::TYPE_DISBURSEMENT,
                'amount' => $facility->disbursed_amount,
                'direction' => 'credit',
                'source' => 'system',
                'reference' => $reference ?: $facility->disbursement_reference,
                'created_at' => Carbon::now(),
            ]);
        });

        // Dispatch AFTER the transaction commits: the down-payment listener may
        // perform an M-Pesa STK HTTP call (mpesa driver), which must not run
        // inside an open DB transaction holding row locks during network I/O.
        FacilityDisbursed::dispatch($facility);

        return $facility;
    }

    /**
     * The financier records that they've released the funds, and how. This is a
     * RECORD-AND-CONFIRM step for every channel: the partner sends the money out
     * of band (you don't STK a financier — the partner is the payer, not someone
     * we collect from), and the payee (Centresidence for installer modules, else
     * the owner) confirms receipt to release the facility for repayment. The
     * channel + reference are just the record of how it was sent.
     *
     * Reference is auto-derived from the facility number when left blank (unique —
     * one disbursement per facility), but stays editable for the real M-Pesa/bank
     * code. (An admin can still skip straight to disburse() for out-of-system.)
     */
    public function recordDisbursement(FinanceFacility $facility, string $channel, ?string $reference = null): FinanceFacility
    {
        if ($facility->isDisbursed()) {
            return $facility;
        }

        $facility->forceFill([
            'disbursement_status'    => FinanceFacility::DISBURSE_PENDING,
            'disbursement_channel'   => $channel,
            'disbursement_reference' => $reference ?: $this->defaultDisbursementReference($facility),
        ])->save();

        return $facility;
    }

    /** A unique, human-readable disbursement reference derived from the facility number. */
    private function defaultDisbursementReference(FinanceFacility $facility): string
    {
        return 'DISB-' . ($facility->facility_number ?: ('FAC' . $facility->id));
    }

    /**
     * The payee (Centresidence for installer modules, else the owner) confirms
     * they received the funds → the facility is officially disbursed and becomes
     * repayable. Only valid from a pending record.
     */
    public function confirmDisbursement(FinanceFacility $facility): FinanceFacility
    {
        return $this->disburse($facility, $facility->disbursement_reference, $facility->disbursement_channel ?: 'manual');
    }
}
