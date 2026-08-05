<?php

namespace App\Services\Payment;

/**
 * M-Pesa STK push (Lipa na M-Pesa Online / C2B) — prompts a customer's phone to
 * authorise a payment INTO a company paybill/till. Centresidence uses this to
 * collect an owner's down-payment for partially-financed modules, since
 * Centresidence is the installer/payee.
 *
 * Mirrors MpesaB2BService. The actual call is only made by
 * DownPaymentCollectionService when the collection driver is 'mpesa'.
 */
class MpesaStkService
{
    use MpesaHelper;

    public function __construct()
    {
        $this->url = config('mpesa.environment') === 'sandbox'
            ? 'https://sandbox.safaricom.co.ke'
            : 'https://api.safaricom.co.ke';

        $this->consumer_key    = config('mpesa.mpesa_consumer_key');
        $this->consumer_secret = config('mpesa.mpesa_consumer_secret');
        $this->shortcode       = config('mpesa.shortcode');
        $this->passkey         = config('mpesa.passkey');
        // Let the per-call callback URL win (the trait falls back to this).
        $this->stkcallback     = null;
    }

    /**
     * @param  \App\Models\MpesaAccount  $account  the company collection account
     * @return array{success:bool, message:string, reference:?string, response:array}
     */
    public function push(string $phone, float $amount, $account, string $description, string $callbackUrl): array
    {
        $response = $this->stkpush($phone, (int) round($amount), $account, $description, $callbackUrl);
        $result   = json_decode((string) $response, true) ?? [];

        $success = isset($result['ResponseCode']) && (string) $result['ResponseCode'] === '0';

        return [
            'success'   => $success,
            'message'   => $result['ResponseDescription'] ?? $result['errorMessage'] ?? (string) $response,
            'reference' => $result['CheckoutRequestID'] ?? null,
            'response'  => $result,
        ];
    }
}
