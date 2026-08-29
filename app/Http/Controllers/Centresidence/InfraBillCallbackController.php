<?php

namespace App\Http\Controllers\Centresidence;

use App\Centresidence\Services\InfraBillPaymentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * M-Pesa STK callback for an owner's module-infrastructure bill payment. On
 * success the owner's outstanding infra invoices are marked paid (idempotent),
 * which clears their infra standing and lifts the readonly gate. Mirrors
 * DownPaymentCallbackController.
 */
class InfraBillCallbackController extends Controller
{
    public function __invoke(Request $request, int $owner, InfraBillPaymentService $bills)
    {
        $body       = json_decode($request->getContent(), true) ?? [];
        $callback   = $body['Body']['stkCallback'] ?? [];
        $resultCode = $callback['ResultCode'] ?? -1;

        if ((int) $resultCode === 0) {
            // Authenticity: only settle against a push WE initiated. A forged/replayed
            // callback finds no matching pending row → clears nothing (debt stays, gate
            // stays down).
            $pending = \App\Centresidence\Models\StkPending::claim(
                'infra_bill',
                $callback['CheckoutRequestID'] ?? null,
                ['owner_user_id' => $owner]
            );

            if (! $pending) {
                Log::warning('Centresidence infra-bill STK callback rejected: no matching pending push', [
                    'owner_user_id' => $owner,
                    'checkout_request_id' => $callback['CheckoutRequestID'] ?? null,
                ]);

                return $this->ack();
            }

            $bills->markPaid($owner, $this->receipt($callback));
        } else {
            Log::info('Centresidence infra-bill STK declined/failed', [
                'owner_user_id' => $owner, 'result_code' => $resultCode,
                'desc' => $callback['ResultDesc'] ?? null,
            ]);
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
