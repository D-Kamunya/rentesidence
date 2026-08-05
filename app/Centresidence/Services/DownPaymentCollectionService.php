<?php

namespace App\Centresidence\Services;

use App\Centresidence\Events\DownPaymentCollected;
use App\Centresidence\Models\FacilityTransaction;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Support\Money;
use App\Models\MpesaAccount;
use App\Models\User;
use App\Services\Payment\MpesaStkService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Collects the owner's down-payment for a partially-financed facility.
 *
 * Centresidence is the official installer/payee, so when a facility carries an
 * owner_contribution > 0 the owner pays that share to Centresidence at
 * disbursement — together with the partner-financed portion this fully settles
 * the deployment cost. Driver-gated like partner payouts:
 *
 *   'log'   — record the collection as settled (no real charge). Default; keeps
 *             the simulation/dev flow closed so no facility is left open.
 *   'mpesa' — real STK push to the owner's phone into the company rent account;
 *             stays 'pending' until the M-Pesa callback confirms it.
 */
class DownPaymentCollectionService
{
    public const STATUS_NOT_REQUIRED = 'not_required';
    public const STATUS_PENDING      = 'pending';
    public const STATUS_COLLECTED    = 'collected';
    public const STATUS_FAILED       = 'failed';

    /** Initiate collection for a facility (idempotent). */
    public function collect(FinanceFacility $facility): FinanceFacility
    {
        if ($facility->down_payment_status === self::STATUS_COLLECTED) {
            return $facility;
        }

        $contribution = Money::fromDecimal((string) ($facility->owner_contribution ?? 0));
        if ($contribution->isZero()) {
            return $this->setStatus($facility, self::STATUS_NOT_REQUIRED);
        }

        if (config('centresidence.collections.driver', 'log') === 'mpesa') {
            return $this->collectViaMpesa($facility, $contribution);
        }

        // 'log' driver: settle immediately so nothing is left open.
        return $this->markCollected($facility, 'LOG-' . now()->timestamp);
    }

    /**
     * Mark a down-payment collected and write the ledger entry. Idempotent —
     * called by the 'log' driver inline and by the M-Pesa callback on confirm.
     */
    public function markCollected(FinanceFacility $facility, ?string $reference = null): FinanceFacility
    {
        return DB::transaction(function () use ($facility, $reference) {
            if ($facility->down_payment_status === self::STATUS_COLLECTED) {
                return $facility;
            }

            FacilityTransaction::create([
                'finance_facility_id' => $facility->id,
                'transaction_type'    => FacilityTransaction::TYPE_DOWN_PAYMENT,
                'amount'              => $facility->owner_contribution,
                'direction'           => 'credit',
                'source'              => 'owner_payment',
                'reference'           => $reference,
                'notes'               => 'Owner down-payment collected by Centresidence (installer).',
                'created_at'          => Carbon::now(),
            ]);

            $facility->forceFill([
                'down_payment_status'       => self::STATUS_COLLECTED,
                'down_payment_reference'    => $reference,
                'down_payment_collected_at' => Carbon::now(),
            ])->save();

            DownPaymentCollected::dispatch($facility);

            return $facility;
        });
    }

    private function collectViaMpesa(FinanceFacility $facility, Money $contribution): FinanceFacility
    {
        $phone = optional(User::find($facility->owner_id))->contact_number;
        $account = ($accountId = getOption('centresidence_rent_mpesa_account_id'))
            ? MpesaAccount::find($accountId)
            : null;

        if (! $phone || ! $account) {
            Log::warning('Centresidence down-payment STK skipped: missing owner phone or collection account', [
                'facility_id' => $facility->id, 'has_phone' => (bool) $phone, 'has_account' => (bool) $account,
            ]);

            return $this->setStatus($facility, self::STATUS_FAILED);
        }

        $callbackUrl = route('centresidence.down-payment.callback', ['facility' => $facility->id]);

        $result = app(MpesaStkService::class)->push(
            $phone,
            (float) $contribution->toDecimal(),
            $account,
            'Module down-payment ' . $facility->facility_number,
            $callbackUrl
        );

        if (! $result['success']) {
            Log::warning('Centresidence down-payment STK push failed', [
                'facility_id' => $facility->id, 'message' => $result['message'] ?? null,
            ]);

            return $this->setStatus($facility, self::STATUS_FAILED);
        }

        // Stays pending until the STK callback confirms the charge.
        return $this->setStatus($facility, self::STATUS_PENDING, $result['reference'] ?? null);
    }

    private function setStatus(FinanceFacility $facility, string $status, ?string $reference = null): FinanceFacility
    {
        $facility->forceFill(array_filter([
            'down_payment_status'    => $status,
            'down_payment_reference' => $reference,
        ], fn ($v) => $v !== null))->save();

        return $facility;
    }
}
