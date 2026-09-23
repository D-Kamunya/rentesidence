<?php

namespace App\Centresidence\Services;

use App\Centresidence\Events\RentCollected;
use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Centresidence\Models\FacilityTransaction;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Models\OwnerInfrastructureInvoice;
use App\Centresidence\Models\SettlementCycle;
use App\Centresidence\Models\SettlementTransaction;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Applies the Deduction Engine's plan to a rent collection (handbook §9.5/§9.6):
 * recovers overdue commission, repays active facilities (penalty → interest →
 * principal), records the settlement ledger + cycles, and reduces the owner's
 * wallet by the total deducted. Idempotent per rent transaction.
 *
 * The actual payout of deducted funds to partners is the M-Pesa B2C remittance
 * step (PartnerRemittanceService); here we only record what is owed.
 */
class RentSettlementService
{
    public function __construct(
        private DeductionEngine $deductionEngine,
        private FacilityInterestService $interest
    ) {
    }

    /**
     * @param  array{rent_transaction_id?:int, settlement_method?:string}  $opts
     * @return array|null  the applied plan summary, or null if already processed / nothing to do
     */
    public function handleRentPayment(int $propertyId, int $ownerId, Money $grossRent, array $opts = []): ?array
    {
        $rentTxnId = $opts['rent_transaction_id'] ?? null;

        // Idempotency: never deduct twice for the same rent transaction.
        if ($rentTxnId && SettlementTransaction::where('rent_transaction_id', $rentTxnId)->exists()) {
            return null;
        }

        $fallbackInvoices = CentresidenceCommissionInvoice::query()
            ->where('property_id', $propertyId)
            ->where('fallback_deduction_active', true)
            ->whereNull('fallback_metered_cleared_at')
            ->orderBy('billing_month')
            ->get();

        // Unpaid infrastructure invoices (transaction owners) — recovered from rent.
        $infraInvoices = OwnerInfrastructureInvoice::query()
            ->where('property_id', $propertyId)
            ->unpaid()
            ->orderBy('billing_month')
            ->get();

        $facilities = FinanceFacility::query()
            ->where('property_id', $propertyId)
            ->where('status', FinanceFacility::STATUS_ACTIVE)
            ->disbursed() // never repay a facility whose funds were never released
            ->get();

        if ($fallbackInvoices->isEmpty() && $infraInvoices->isEmpty() && $facilities->isEmpty()) {
            return null; // property has no Centresidence obligations
        }

        // Accrue interest to date so the split reflects what's actually owed,
        // and compute each facility's remaining cap for this settlement cycle
        // (skipped for accelerated facilities, which deduct until cleared).
        $cycleCaps = [];
        foreach ($facilities as $facility) {
            $this->interest->syncOutstandingInterest($facility);
            if (! $facility->accelerated_repayment) {
                $cycleCaps[$facility->id] = $this->cycleRemaining($facility);
            }
        }

        $plan = $this->deductionEngine->plan($grossRent, $fallbackInvoices, $infraInvoices, $facilities, $cycleCaps);

        if (! $plan['total_deducted']->isPositive()) {
            return null;
        }

        return DB::transaction(function () use ($plan, $fallbackInvoices, $ownerId, $rentTxnId, $opts) {
            $method = $opts['settlement_method'] ?? 'mobile_money';

            $this->applyFallback($plan['fallback'], $fallbackInvoices, $rentTxnId, $method);

            $this->applyInfraRecovery($plan['infra_plans'], $rentTxnId);

            foreach ($plan['facilities'] as $fp) {
                $this->applyFacilityRepayment($fp, $rentTxnId, $method);
            }

            $this->decrementOwnerWallet($ownerId, $plan['total_deducted'], $rentTxnId);

            RentCollected::dispatch($ownerId, $plan['rent']->toDecimal(), $plan['total_deducted']->toDecimal(), $rentTxnId);

            return [
                'fallback' => $plan['fallback']->toDecimal(),
                'infrastructure' => $plan['infrastructure']->toDecimal(),
                'facilities_deducted' => count($plan['facilities']),
                'total_deducted' => $plan['total_deducted']->toDecimal(),
                'owner_net' => $plan['owner_net']->toDecimal(),
            ];
        });
    }

    // ── Fallback (metered commission recovery from rent) ──────────────────

    private function applyFallback(Money $fallbackTake, $invoices, ?int $rentTxnId, string $method): void
    {
        if (! $fallbackTake->isPositive()) {
            return;
        }

        $remaining = $fallbackTake;
        foreach ($invoices as $invoice) {
            $outstanding = $invoice->meteredOutstanding();
            if (! $outstanding->isPositive()) {
                continue;
            }
            $take = $remaining->cappedAt($outstanding);
            if (! $take->isPositive()) {
                break;
            }

            $invoice->metered_paid_total = Money::fromDecimal($invoice->metered_paid_total ?? '0')->plus($take)->toDecimal();
            if (! $invoice->meteredOutstanding()->isPositive()) {
                $invoice->fallback_deduction_active = false;
                $invoice->fallback_metered_cleared_at = Carbon::now();
                $invoice->status = $invoice->hasNonMetered()
                    ? CentresidenceCommissionInvoice::STATUS_PARTIALLY_PAID
                    : CentresidenceCommissionInvoice::STATUS_PAID;
            }
            $invoice->save();

            SettlementTransaction::create([
                'transaction_type' => 'commission_recovery',
                'amount' => $take->toDecimal(),
                'beneficiary_type' => SettlementTransaction::BENEFICIARY_CENTRESIDENCE,
                'settlement_method' => 'internal',
                'rent_transaction_id' => $rentTxnId,
                'created_at' => Carbon::now(),
            ]);

            $remaining = $remaining->minus($take);
            if (! $remaining->isPositive()) {
                break;
            }
        }
    }

    /**
     * Recover infrastructure costs (software + gateway) from rent for a
     * transaction owner — credits Centresidence, marks the invoice paid/partial.
     *
     * @param  array<int,array{invoice:OwnerInfrastructureInvoice, amount:Money}>  $infraPlans
     */
    private function applyInfraRecovery(array $infraPlans, ?int $rentTxnId): void
    {
        foreach ($infraPlans as $ip) {
            $invoice = $ip['invoice'];
            $take = $ip['amount'];
            if (! $take->isPositive()) {
                continue;
            }

            $invoice->paid_total = Money::fromDecimal($invoice->paid_total ?? '0')->plus($take)->toDecimal();
            if (! $invoice->outstanding()->isPositive()) {
                $invoice->status = OwnerInfrastructureInvoice::STATUS_PAID;
                $invoice->paid_at = Carbon::now();
            } else {
                $invoice->status = OwnerInfrastructureInvoice::STATUS_PARTIALLY_PAID;
            }
            $invoice->save();

            SettlementTransaction::create([
                'transaction_type' => 'infrastructure_recovery',
                'amount' => $take->toDecimal(),
                'beneficiary_type' => SettlementTransaction::BENEFICIARY_CENTRESIDENCE,
                'settlement_method' => 'internal',
                'rent_transaction_id' => $rentTxnId,
                'created_at' => Carbon::now(),
            ]);
        }
    }

    // ── Facility repayment ────────────────────────────────────────────────

    private function applyFacilityRepayment(array $fp, ?int $rentTxnId, string $method): void
    {
        /** @var FinanceFacility $facility */
        $facility = $fp['facility'];
        $cycle = $this->currentCycle($facility);

        $components = [
            FacilityTransaction::TYPE_REPAYMENT_PENALTY => ['amount' => $fp['penalty'], 'column' => 'outstanding_penalty'],
            FacilityTransaction::TYPE_REPAYMENT_INTEREST => ['amount' => $fp['interest'], 'column' => 'outstanding_interest'],
            FacilityTransaction::TYPE_REPAYMENT_PRINCIPAL => ['amount' => $fp['principal'], 'column' => 'outstanding_principal'],
        ];

        foreach ($components as $type => $c) {
            if (! $c['amount']->isPositive()) {
                continue;
            }

            $facility->{$c['column']} = Money::fromDecimal($facility->{$c['column']})->minus($c['amount'])->toDecimal();

            FacilityTransaction::create([
                'finance_facility_id' => $facility->id,
                'transaction_type' => $type,
                'amount' => $c['amount']->toDecimal(),
                'direction' => 'debit',
                'source' => 'rent_deduction',
                'rent_transaction_id' => $rentTxnId,
                'created_at' => Carbon::now(),
            ]);

            SettlementTransaction::create([
                'settlement_cycle_id' => $cycle->id,
                'finance_facility_id' => $facility->id,
                'transaction_type' => str_replace('repayment_', 'rent_deduction_', $type),
                'amount' => $c['amount']->toDecimal(),
                'beneficiary_type' => SettlementTransaction::BENEFICIARY_PARTNER,
                'beneficiary_id' => $facility->finance_partner_id,
                'settlement_method' => $method,
                'rent_transaction_id' => $rentTxnId,
                'created_at' => Carbon::now(),
            ]);
        }

        // Mark facility complete if fully repaid.
        if (! $facility->outstandingTotal()->isPositive()) {
            $facility->status = FinanceFacility::STATUS_COMPLETED;
            $facility->completed_at = Carbon::now();
        }
        $facility->save();

        $this->applyToSchedules($facility, $fp['interest'], $fp['principal']);

        $cycle->collected_amount = Money::fromDecimal($cycle->collected_amount)->plus($fp['amount'])->toDecimal();
        $cycle->save();
    }

    private function applyToSchedules(FinanceFacility $facility, Money $interest, Money $principal): void
    {
        $intPool = $interest;
        $prinPool = $principal;

        foreach ($facility->schedules()->where('status', '!=', 'paid')->orderBy('period_number')->get() as $row) {
            if (! $intPool->isPositive() && ! $prinPool->isPositive()) {
                break;
            }

            $intDueRemaining = Money::fromDecimal($row->interest_due)->minus(Money::fromDecimal($row->interest_paid));
            $payInt = $intPool->cappedAt($intDueRemaining->isPositive() ? $intDueRemaining : Money::zero());

            $prinDueRemaining = Money::fromDecimal($row->principal_due)->minus(Money::fromDecimal($row->principal_paid));
            $payPrin = $prinPool->cappedAt($prinDueRemaining->isPositive() ? $prinDueRemaining : Money::zero());

            $row->interest_paid = Money::fromDecimal($row->interest_paid)->plus($payInt)->toDecimal();
            $row->principal_paid = Money::fromDecimal($row->principal_paid)->plus($payPrin)->toDecimal();
            $totalPaid = Money::fromDecimal($row->principal_paid)
                ->plus(Money::fromDecimal($row->interest_paid))
                ->plus(Money::fromDecimal($row->penalty_paid));
            $row->total_paid = $totalPaid->toDecimal();
            // Paid when total_paid >= total_due; partial when some paid; else pending.
            $shortfall = Money::fromDecimal($row->total_due)->minus($totalPaid);
            $row->status = ! $shortfall->isPositive()
                ? 'paid'
                : ($totalPaid->isPositive() ? 'partial' : 'pending');
            $row->save();

            $intPool = $intPool->minus($payInt);
            $prinPool = $prinPool->minus($payPrin);
        }
    }

    private function currentCycle(FinanceFacility $facility): SettlementCycle
    {
        return SettlementCycle::firstOrCreate(
            [
                'finance_facility_id' => $facility->id,
                'finance_partner_id' => $facility->finance_partner_id,
                // Match on the Carbon (start of month) so the lookup equals the
                // stored datetime on re-run (date cast persists Y-m-d 00:00:00).
                'cycle_start' => Carbon::now()->startOfMonth(),
            ],
            [
                'settlement_type' => 'monthly',
                'cycle_end' => Carbon::now()->endOfMonth()->toDateString(),
                'expected_amount' => $facility->monthly_target,
                'status' => 'collecting',
            ]
        );
    }

    /** Remaining amount that may still be collected for this facility's cycle. */
    private function cycleRemaining(FinanceFacility $facility): Money
    {
        $monthStart = Carbon::now()->startOfMonth()->toDateString();
        $cycle = SettlementCycle::query()
            ->where('finance_facility_id', $facility->id)
            ->whereDate('cycle_start', $monthStart)
            ->first();

        $collected = $cycle ? Money::fromDecimal($cycle->collected_amount) : Money::zero();
        $remaining = Money::fromDecimal($facility->monthly_target)->minus($collected);

        return $remaining->isPositive() ? $remaining : Money::zero();
    }

    private function decrementOwnerWallet(int $ownerId, Money $amount, ?int $rentTxnId = null): void
    {
        // Live integration: the owner's rent net was credited to their wallet by
        // the existing rent flow; reduce it by what Centresidence deducted.
        if (! $amount->isPositive() || ! Schema::hasTable('owner_wallets')) {
            return;
        }

        $wallet = \App\Models\OwnerWallet::forUser($ownerId);
        $wallet->decrement('balance', (float) $amount->toDecimal());

        // Fold the deduction into the SINGLE rent payment ledger line rather than a
        // separate debit row (which would read as a withdrawal and double the entry
        // for one payment event). The payment credit's net_amount drops by what was
        // deducted, so net = gross − commission − deductions and the balance stays
        // reconciled (balance = Σ net_amount). The modal itemises the deductions from
        // the settlement transactions (gross − commission − net = total deducted).
        if ($rentTxnId && Schema::hasTable('wallet_transactions')) {
            \App\Models\WalletTransaction::where('owner_wallet_id', $wallet->id)
                ->where('invoice_order_id', $rentTxnId)
                ->where('type', 'credit')
                ->where('transaction_source', 'rent')
                ->decrement('net_amount', (float) $amount->toDecimal());
        }
    }
}
