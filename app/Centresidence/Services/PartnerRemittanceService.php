<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\PartnerRemittanceBatch;
use App\Centresidence\Models\PartnerRemittanceBatchItem;
use App\Centresidence\Models\SettlementTransaction;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Partner Remittance (handbook §9.5.4) — aggregates the settlement transactions
 * owed to a partner into a single payout batch. This is the M-Pesa B2B seam:
 * `markSent()` is where the real B2B disbursement to the partner's M-Pesa
 * till/paybill (banks/SACCOs have paybills) is triggered; a direct bank rail
 * (PesaLink/RTGS) can replace it later behind the same call.
 */
class PartnerRemittanceService
{
    public function __construct(private PartnerPayoutService $payouts)
    {
    }

    /**
     * Build a "prepared" remittance batch from all pending partner-owed
     * settlement transactions. Returns null if there is nothing to remit.
     */
    public function prepareBatchForPartner(int $partnerId, string $method = 'mobile_money'): ?PartnerRemittanceBatch
    {
        $transactions = SettlementTransaction::query()
            ->pendingForPartner($partnerId)
            ->get();

        if ($transactions->isEmpty()) {
            return null;
        }

        return DB::transaction(function () use ($partnerId, $method, $transactions) {
            $total = $transactions->reduce(
                fn (Money $c, $t) => $c->plus(Money::fromDecimal($t->amount)),
                Money::zero()
            );

            $batch = PartnerRemittanceBatch::create([
                'finance_partner_id' => $partnerId,
                'remittance_date' => Carbon::now()->toDateString(),
                'total_amount' => $total->toDecimal(),
                'facility_count' => $transactions->pluck('finance_facility_id')->unique()->count(),
                'transaction_count' => $transactions->count(),
                'settlement_method' => $method,
                'status' => PartnerRemittanceBatch::STATUS_PREPARED,
            ]);

            $batch->forceFill([
                'batch_number' => 'REM-' . now()->year . '-' . str_pad((string) $batch->id, 5, '0', STR_PAD_LEFT),
            ])->save();

            foreach ($transactions as $txn) {
                PartnerRemittanceBatchItem::create([
                    'partner_remittance_batch_id' => $batch->id,
                    'settlement_transaction_id' => $txn->id,
                    'facility_id' => $txn->finance_facility_id,
                    'amount' => $txn->amount,
                    'created_at' => Carbon::now(),
                ]);

                $txn->update(['reconciliation_status' => SettlementTransaction::RECON_RECONCILED]);
            }

            return $batch;
        });
    }

    /**
     * Run remittances for all partners that are DUE today per their configured
     * cadence (repayment_frequency / settlement_day on their products). Returns
     * the batches prepared. Called daily by the scheduler.
     *
     * @return array<int,PartnerRemittanceBatch>
     */
    public function runDueRemittances(?Carbon $today = null): array
    {
        $today = $today ?? Carbon::now();
        $batches = [];

        foreach (FinancePartner::query()->active()->get() as $partner) {
            if (! $this->isDueToday($partner, $today)) {
                continue;
            }
            $batch = $this->prepareBatchForPartner($partner->id);
            if ($batch) {
                $batches[] = $this->payBatch($batch);
            }
        }

        return $batches;
    }

    /**
     * Execute the payout for a prepared (or previously failed) batch via the
     * configured driver, and record the outcome. Idempotent: a batch already
     * sent/confirmed is left untouched.
     */
    public function payBatch(PartnerRemittanceBatch $batch): PartnerRemittanceBatch
    {
        if (! in_array($batch->status, [PartnerRemittanceBatch::STATUS_PREPARED, PartnerRemittanceBatch::STATUS_FAILED], true)) {
            return $batch;
        }

        $result = $this->payouts->pay($batch);

        if ($result['success']) {
            $batch->update([
                'status' => PartnerRemittanceBatch::STATUS_SENT,
                'reference' => $result['reference'],
            ]);
        } else {
            $batch->update([
                'status' => PartnerRemittanceBatch::STATUS_FAILED,
                'notes' => $result['message'],
            ]);
        }

        return $batch;
    }

    /**
     * Whether a partner is due for remittance today, by their tightest product
     * cadence: daily settlement → always due; monthly → only on settlement_day.
     */
    public function isDueToday(FinancePartner $partner, Carbon $today): bool
    {
        $products = FinancePartnerModule::query()
            ->where('finance_partner_id', $partner->id)
            ->where('status', FinancePartnerModule::STATUS_ACTIVE)
            ->get();

        if ($products->isEmpty()) {
            return false;
        }

        // Daily settlement enabled on any product → settle directly every day.
        if ($products->contains(fn ($p) => (bool) $p->daily_settlement_enabled)) {
            return true;
        }

        // Otherwise monthly: due on the configured settlement_day (default 1).
        foreach ($products as $product) {
            if ($product->monthly_settlement_enabled) {
                $day = (int) ($product->settlement_day ?: 1);
                if ($today->day === min($day, $today->daysInMonth)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Mark a batch as sent. INTEGRATION POINT: trigger the M-Pesa B2B payout to
     * the partner's settlement account (settlement_account_details) here, then
     * record the provider reference.
     */
    public function markSent(PartnerRemittanceBatch $batch, ?string $reference = null): PartnerRemittanceBatch
    {
        $batch->update([
            'status' => PartnerRemittanceBatch::STATUS_SENT,
            'reference' => $reference,
        ]);

        return $batch;
    }

    /**
     * Confirm a payout from the M-Pesa B2B result callback: money actually landed.
     * Idempotent — only a batch still awaiting confirmation (SENT) transitions, so
     * a re-fired callback is a no-op and never double-confirms.
     */
    public function confirmBatch(PartnerRemittanceBatch $batch, ?string $receipt = null): PartnerRemittanceBatch
    {
        if ($batch->status !== PartnerRemittanceBatch::STATUS_SENT) {
            return $batch;
        }

        $batch->update([
            'status' => PartnerRemittanceBatch::STATUS_CONFIRMED,
            'confirmation_received_at' => Carbon::now(),
            'reference' => $receipt ?: $batch->reference, // upgrade the provisional ConversationID to the receipt
        ]);

        return $batch;
    }

    /**
     * Fail a payout from the callback (or timeout): the transfer did not go through.
     * Idempotent and retryable — status returns to FAILED, which payBatch() will
     * re-attempt on the next run (the same batch, same transactions).
     */
    public function failBatch(PartnerRemittanceBatch $batch, string $reason): PartnerRemittanceBatch
    {
        if ($batch->status !== PartnerRemittanceBatch::STATUS_SENT) {
            return $batch;
        }

        $batch->update([
            'status' => PartnerRemittanceBatch::STATUS_FAILED,
            'notes' => $reason,
        ]);

        return $batch;
    }
}
