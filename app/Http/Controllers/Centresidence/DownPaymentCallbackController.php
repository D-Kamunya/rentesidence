<?php

namespace App\Http\Controllers\Centresidence;

use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Services\DownPaymentCollectionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * M-Pesa STK callback for an owner down-payment (partial financing). Safaricom
 * POSTs the result of the STK push initiated by DownPaymentCollectionService.
 * On success we confirm the collection (idempotent); on failure we flag it so
 * the facility's outstanding down-payment is visible to admins.
 */
class DownPaymentCallbackController extends Controller
{
    public function __invoke(Request $request, int $facility, DownPaymentCollectionService $collections)
    {
        $body = json_decode($request->getContent(), true) ?? [];
        $callback = $body['Body']['stkCallback'] ?? [];
        $resultCode = $callback['ResultCode'] ?? -1;

        $facilityModel = FinanceFacility::find($facility);
        if (! $facilityModel) {
            return $this->ack();
        }

        // Authenticity: the callback must echo the CheckoutRequestID we stored on this
        // facility when the STK push was initiated (down_payment_reference). Reject a
        // forged result that doesn't match — otherwise anyone could mark a facility's
        // down-payment collected (or failed) without a real payment.
        $callbackCid = $callback['CheckoutRequestID'] ?? null;
        if (empty($facilityModel->down_payment_reference)
            || empty($callbackCid)
            || ! hash_equals((string) $facilityModel->down_payment_reference, (string) $callbackCid)) {
            Log::warning('Centresidence down-payment callback rejected: reference mismatch', [
                'facility_id' => $facility,
                'checkout_request_id' => $callbackCid,
            ]);

            return $this->ack();
        }

        if ((int) $resultCode === 0) {
            $collections->markCollected($facilityModel, $this->receipt($callback));
        } else {
            Log::info('Centresidence down-payment STK declined/failed', [
                'facility_id' => $facility, 'result_code' => $resultCode,
                'desc' => $callback['ResultDesc'] ?? null,
            ]);
            $facilityModel->forceFill(['down_payment_status' => DownPaymentCollectionService::STATUS_FAILED])->save();
        }

        return $this->ack();
    }

    /** Pull the M-Pesa receipt number from the callback metadata, if present. */
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
