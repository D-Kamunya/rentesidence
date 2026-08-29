<?php

namespace App\Http\Controllers\Centresidence;

use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Services\FinanceFacilityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * M-Pesa STK callback for an owner's early-settlement payoff. On a confirmed
 * charge the facility is completed and the partner's payoff is recorded for
 * remittance (idempotent). Authenticated by echoing back the CheckoutRequestID
 * we stored when the STK was pushed — a forged result can't close a facility.
 */
class SettleEarlyCallbackController extends Controller
{
    public function __invoke(Request $request, int $facility, FinanceFacilityService $facilities)
    {
        $body = json_decode($request->getContent(), true) ?? [];
        $callback = $body['Body']['stkCallback'] ?? [];
        $resultCode = $callback['ResultCode'] ?? -1;

        $facilityModel = FinanceFacility::find($facility);
        if (! $facilityModel) {
            return $this->ack();
        }

        $callbackCid = $callback['CheckoutRequestID'] ?? null;
        if (empty($facilityModel->early_settlement_reference)
            || empty($callbackCid)
            || ! hash_equals((string) $facilityModel->early_settlement_reference, (string) $callbackCid)) {
            Log::warning('Centresidence settle-early callback rejected: reference mismatch', [
                'facility_id' => $facility, 'checkout_request_id' => $callbackCid,
            ]);
            return $this->ack();
        }

        if ((int) $resultCode === 0) {
            $facilities->confirmEarlySettlement($facilityModel, $this->receipt($callback));
        } else {
            Log::info('Centresidence settle-early STK declined/failed', [
                'facility_id' => $facility, 'result_code' => $resultCode, 'desc' => $callback['ResultDesc'] ?? null,
            ]);
            // Leave the facility active + pending so the owner can retry; nothing was paid.
        }

        return $this->ack();
    }

    private function receipt(array $callback): ?string
    {
        foreach ($callback['CallbackMetadata']['Item'] ?? [] as $item) {
            if (($item['Name'] ?? null) === 'MpesaReceiptNumber') {
                return (string) ($item['Value'] ?? '');
            }
        }
        return $callback['CheckoutRequestID'] ?? null;
    }

    private function ack()
    {
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
