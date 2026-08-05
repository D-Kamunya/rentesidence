<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Models\MpesaAccount;
use App\Models\User;
use App\Services\Payment\MpesaStkService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Collects a subscription-mode owner's outstanding MODULE-INFRA bill via M-Pesa STK
 * (into the Centresidence collection account, same rail as the owner down-payment).
 * On the STK callback the settled invoices are marked paid, which clears the owner's
 * infra standing and lifts the readonly gate. Only the infra portion (metered +
 * non-metered) is charged here — the plan is collected on the subscription side
 * (the stage-4 merge unifies them into a single charge).
 */
class InfraBillPaymentService
{
    /** The owner's unpaid infra invoices + the total infra amount owed. */
    public function outstanding(int $ownerUserId): array
    {
        $invoices = CentresidenceCommissionInvoice::query()
            ->where('owner_id', $ownerUserId)
            ->unpaid()
            ->get()
            ->filter(fn ($inv) => $this->infraOf($inv) > 0)
            ->values();

        $total = round($invoices->sum(fn ($inv) => $this->infraOf($inv)), 2);

        return ['invoices' => $invoices, 'total' => $total];
    }

    /**
     * Initiate the STK charge for the owner's outstanding infra.
     * @return array{success:bool, message:string, reference:?string}
     */
    public function initiate(int $ownerUserId): array
    {
        $out = $this->outstanding($ownerUserId);
        if ($out['total'] <= 0) {
            return ['success' => false, 'message' => 'No infrastructure balance is due.', 'reference' => null];
        }

        // Driver-gated like the down-payment collection. 'log' (dev/simulation) settles
        // immediately with no real STK — this is what keeps dev closed and avoids firing
        // a live M-Pesa call (which fails with a bad-callback error when unconfigured).
        if (config('centresidence.collections.driver', 'log') !== 'mpesa') {
            $this->markPaid($ownerUserId, 'LOG-' . now()->timestamp);

            return ['success' => true, 'message' => __('Infrastructure bill settled.'), 'reference' => 'LOG', 'settled' => true];
        }

        $phone   = optional(User::find($ownerUserId))->contact_number;
        $account = ($accountId = getOption('centresidence_rent_mpesa_account_id'))
            ? MpesaAccount::find($accountId)
            : null;

        if (! $phone || ! $account) {
            Log::warning('Centresidence infra-bill STK skipped: missing owner phone or collection account', [
                'owner_user_id' => $ownerUserId, 'has_phone' => (bool) $phone, 'has_account' => (bool) $account,
            ]);

            return ['success' => false, 'message' => 'We could not start the payment — please contact support.', 'reference' => null];
        }

        return app(MpesaStkService::class)->push(
            $phone,
            (float) $out['total'],
            $account,
            'Module infrastructure bill',
            route('centresidence.infra-bill.callback', ['owner' => $ownerUserId])
        );
    }

    /**
     * Mark the owner's outstanding infra invoices paid (called by the STK callback
     * on success). Idempotent — a re-fired callback finds nothing unpaid and no-ops.
     * Clears the metered token-recovery fallback too. Returns the number settled.
     */
    public function markPaid(int $ownerUserId, ?string $reference = null): int
    {
        return DB::transaction(function () use ($ownerUserId) {
            $invoices = CentresidenceCommissionInvoice::query()
                ->where('owner_id', $ownerUserId)
                ->unpaid()
                ->lockForUpdate()
                ->get();

            $count = 0;
            foreach ($invoices as $inv) {
                if ($this->infraOf($inv) <= 0) {
                    continue;
                }

                $inv->forceFill([
                    'status'                      => CentresidenceCommissionInvoice::STATUS_PAID,
                    'paid_at'                     => now(),
                    'metered_paid_total'          => $inv->metered_commission_total,
                    'fallback_deduction_active'   => false,
                    'fallback_metered_cleared_at' => now(),
                ])->save();
                $count++;
            }

            return $count;
        });
    }

    /**
     * The infra the OWNER still owes on this invoice: the non-metered (fixed) cost
     * plus only the metered portion not already recovered from token revenue by the
     * fallback. Netting out `metered_paid_total` is what stops the owner being
     * double-charged for metered infra the fallback already collected (completion-map
     * C1 reconciliation). `markPaid` then settles this remainder + marks it paid.
     */
    private function infraOf(CentresidenceCommissionInvoice $inv): float
    {
        $meteredOutstanding = max(
            0.0,
            (float) $inv->metered_commission_total - (float) ($inv->metered_paid_total ?? 0)
        );

        return $meteredOutstanding + (float) $inv->non_metered_commission_total;
    }
}
