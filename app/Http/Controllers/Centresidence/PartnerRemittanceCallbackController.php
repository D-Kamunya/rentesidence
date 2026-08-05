<?php

namespace App\Http\Controllers\Centresidence;

use App\Centresidence\Models\PartnerRemittanceBatch;
use App\Centresidence\Services\PartnerRemittanceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * M-Pesa B2B result callback for a partner remittance. Safaricom POSTs the async
 * outcome of the B2B payout initiated by PartnerPayoutService. On success we
 * confirm the batch (money landed); on failure we flag it FAILED so it's retried.
 * Idempotent — the service only transitions a batch still awaiting confirmation,
 * so a re-fired callback never double-confirms. Mirrors DownPaymentCallbackController.
 */
class PartnerRemittanceCallbackController extends Controller
{
    public function __invoke(Request $request, int $batch, PartnerRemittanceService $remittances)
    {
        $body   = json_decode($request->getContent(), true) ?? [];
        $result = $body['Result'] ?? [];
        $code   = $result['ResultCode'] ?? -1;

        $batchModel = PartnerRemittanceBatch::find($batch);
        if (! $batchModel) {
            return $this->ack();
        }

        if ((int) $code === 0) {
            $remittances->confirmBatch($batchModel, $this->receipt($result));
        } else {
            Log::info('Centresidence partner remittance B2B failed', [
                'batch_id' => $batch, 'result_code' => $code,
                'desc' => $result['ResultDesc'] ?? null,
            ]);
            $remittances->failBatch($batchModel, (string) ($result['ResultDesc'] ?? 'B2B payout failed'));
        }

        return $this->ack();
    }

    /** Pull the M-Pesa transaction receipt from the B2B result, if present. */
    private function receipt(array $result): ?string
    {
        foreach ($result['ResultParameters']['ResultParameter'] ?? [] as $param) {
            if (in_array($param['Key'] ?? null, ['TransactionReceipt', 'TransactionID'], true)) {
                return (string) ($param['Value'] ?? '');
            }
        }

        return $result['TransactionID'] ?? null;
    }

    private function ack()
    {
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
