<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\PartnerRemittanceBatch;
use App\Services\Payment\MpesaB2BService;
use Illuminate\Support\Facades\Log;

/**
 * Executes a partner remittance payout. The driver (config
 * `centresidence.payouts.driver`) decides the rail:
 *
 *   - 'log'   → record only, no transfer (safe default until M-Pesa creds are
 *               configured). Returns a simulated reference so the settlement
 *               flow still completes end-to-end.
 *   - 'mpesa' → real M-Pesa B2C (phone) or B2B (paybill/till/bank paybill),
 *               chosen from the partner's settlement_account_details.
 *
 * Bank partners are paid via M-Pesa B2B to their bank paybill (with the account
 * number as reference); a native bank rail (PesaLink/RTGS) can be added here
 * later behind the same `pay()` contract.
 */
class PartnerPayoutService
{
    /**
     * @return array{success:bool, reference:?string, message:string}
     */
    public function pay(PartnerRemittanceBatch $batch): array
    {
        $amount = (float) $batch->total_amount;
        $account = (array) (optional($batch->partner)->settlement_account_details ?? []);
        $driver = config('centresidence.payouts.driver', 'log');

        if ($amount <= 0) {
            return ['success' => false, 'reference' => null, 'message' => 'Nothing to remit.'];
        }

        if ($driver === 'log') {
            $ref = 'LOG-' . strtoupper(uniqid());
            Log::info('Centresidence partner payout (log driver)', [
                'batch' => $batch->batch_number, 'partner_id' => $batch->finance_partner_id,
                'amount' => $amount, 'account' => $account, 'reference' => $ref,
            ]);

            return ['success' => true, 'reference' => $ref, 'message' => 'Recorded (log driver — no transfer made).'];
        }

        if ($driver !== 'mpesa') {
            return ['success' => false, 'reference' => null, 'message' => "Unknown payout driver: {$driver}."];
        }

        if (! config('mpesa.mpesa_consumer_key')) {
            return ['success' => false, 'reference' => null, 'message' => 'M-Pesa is not configured.'];
        }

        try {
            // Per-batch result URL so Safaricom's async B2B outcome is reconciled
            // back to THIS batch (SENT → CONFIRMED/FAILED). Must be dedicated — the
            // shared config result URL routes to the owner-withdrawal B2C handler.
            $resultUrl = route('centresidence.remittance.callback', ['batch' => $batch->id]);

            return $this->payViaMpesa($account, $amount, $resultUrl);
        } catch (\Throwable $e) {
            Log::error('Centresidence partner payout failed', ['batch' => $batch->batch_number, 'error' => $e->getMessage()]);

            return ['success' => false, 'reference' => null, 'message' => $e->getMessage()];
        }
    }

    private function payViaMpesa(array $account, float $amount, ?string $resultUrl = null): array
    {
        switch ($account['type'] ?? null) {
            case 'mpesa_paybill':
            case 'bank':
                // Bank partners are paid via M-Pesa B2B to their bank paybill with the
                // account number as reference — always tied to an account, and the
                // async B2B result is reconciled back to the batch via $resultUrl.
                return app(MpesaB2BService::class)->send((string) $account['paybill'], $amount, (string) ($account['account'] ?? ''), 'BusinessPayBill', $resultUrl);

            case 'mpesa_till':
                return app(MpesaB2BService::class)->send((string) $account['till'], $amount, '', 'BusinessBuyGoods', $resultUrl);

            default:
                // B2C (pay-to-phone) is intentionally unsupported: it can't be reconciled
                // to an account and has no per-call result URL. Partners must set a
                // paybill / bank / till (enforced before they can publish a product).
                return ['success' => false, 'reference' => null, 'message' => 'Set a paybill or bank payout account — pay-to-phone (B2C) is not supported.'];
        }
    }
}
