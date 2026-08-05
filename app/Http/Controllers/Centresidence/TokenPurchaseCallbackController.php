<?php

namespace App\Http\Controllers\Centresidence;

use App\Centresidence\Services\TokenPurchaseCollectionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * M-Pesa STK callback for a tenant utility-token purchase. On success the paid
 * amount (as confirmed by Safaricom, not the requested figure) is settled into
 * wallet units via the TokenEngine — idempotent on the M-Pesa receipt, so a
 * re-fired webhook credits once. Mirrors InfraBillCallbackController.
 *
 * The module + tenant are carried in the callback URL (set by us server-side at
 * push time, hence trustworthy); TokenPurchaseCollectionService re-checks that
 * the module still belongs to the tenant before crediting.
 */
class TokenPurchaseCallbackController extends Controller
{
    public function __invoke(Request $request, int $propertyModule, int $tenant, TokenPurchaseCollectionService $tokens)
    {
        $body       = json_decode($request->getContent(), true) ?? [];
        $callback   = $body['Body']['stkCallback'] ?? [];
        $resultCode = $callback['ResultCode'] ?? -1;

        if ((int) $resultCode === 0) {
            $amount = $this->amount($callback);

            if ($amount > 0) {
                $tokens->settle($propertyModule, $tenant, $amount, $this->receipt($callback));
            } else {
                Log::warning('Centresidence token STK callback missing amount', [
                    'property_module_id' => $propertyModule, 'tenant_user_id' => $tenant,
                ]);
            }
        } else {
            Log::info('Centresidence token STK declined/failed', [
                'property_module_id' => $propertyModule, 'tenant_user_id' => $tenant,
                'result_code' => $resultCode, 'desc' => $callback['ResultDesc'] ?? null,
            ]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    private function amount(array $callback): float
    {
        return (float) ($this->metadata($callback, 'Amount') ?? 0);
    }

    private function receipt(array $callback): ?string
    {
        return $this->metadata($callback, 'MpesaReceiptNumber')
            ?? ($callback['CheckoutRequestID'] ?? null);
    }

    private function metadata(array $callback, string $name)
    {
        foreach ($callback['CallbackMetadata']['Item'] ?? [] as $item) {
            if (($item['Name'] ?? null) === $name) {
                return $item['Value'] ?? null;
            }
        }

        return null;
    }
}
