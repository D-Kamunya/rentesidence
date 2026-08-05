<?php

namespace App\Services\Payment;

/**
 * M-Pesa B2B (Business-to-Business) disbursement — pays out to a paybill or
 * till. This is how Centresidence settles finance partners that receive on a
 * paybill/till (banks and SACCOs almost all have a paybill, with the partner's
 * account number passed as the AccountReference).
 *
 * Mirrors MpesaB2CService. The actual call is only made by PartnerPayoutService
 * when the payout driver is 'mpesa'.
 */
class MpesaB2BService
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
    }

    /**
     * @param  string  $receiverShortcode  partner paybill / till number
     * @param  string  $accountReference    partner's account ref (for paybills)
     * @param  string  $commandId           BusinessPayBill | BusinessBuyGoods
     * @param  ?string $resultUrl           where Safaricom POSTs the async result;
     *                                       the caller passes a per-payout URL so the
     *                                       outcome can be reconciled to that payout
     *                                       (falls back to the shared config URL).
     */
    public function send(string $receiverShortcode, float $amount, string $accountReference = '', string $commandId = 'BusinessPayBill', ?string $resultUrl = null): array
    {
        $payload = [
            'Initiator'              => config('mpesa.initiator_name'),
            'SecurityCredential'     => $this->encryptInitiatorPassword(config('mpesa.initiator_password')),
            'CommandID'              => $commandId,
            'SenderIdentifierType'   => '4',
            'RecieverIdentifierType' => '4',
            'Amount'                 => (int) round($amount),
            'PartyA'                 => config('mpesa.b2c_shortcode') ?: config('mpesa.shortcode'),
            'PartyB'                 => $receiverShortcode,
            'AccountReference'       => $accountReference,
            'Remarks'                => 'Centresidence partner settlement',
            'QueueTimeOutURL'        => $resultUrl ?: config('mpesa.b2c_timeout_url'),
            'ResultURL'              => $resultUrl ?: config('mpesa.b2c_result_url'),
        ];

        $response = $this->MpesaRequest($this->url . '/mpesa/b2b/v1/paymentrequest', $payload);
        $result   = $response->json() ?? [];
        $success  = $response->successful()
            && isset($result['ResponseCode'])
            && (string) $result['ResponseCode'] === '0';

        return [
            'success'   => $success,
            'message'   => $result['ResponseDescription'] ?? $result['errorMessage'] ?? $response->body(),
            'reference' => $result['ConversationID'] ?? $result['OriginatorConversationID'] ?? null,
            'response'  => $result,
        ];
    }

    private function encryptInitiatorPassword(string $plainPassword): string
    {
        $certPath = config('mpesa.environment') === 'sandbox'
            ? storage_path('app/mpesa/sandbox.cer')
            : storage_path('app/mpesa/production.cer');

        if (! file_exists($certPath)) {
            throw new \Exception("M-Pesa B2B certificate not found at: {$certPath}");
        }

        $pubKey = openssl_get_publickey(file_get_contents($certPath));
        if (! $pubKey) {
            throw new \Exception("M-Pesa B2B certificate could not be parsed at: {$certPath}");
        }

        openssl_public_encrypt($plainPassword, $encrypted, $pubKey, OPENSSL_PKCS1_PADDING);

        return base64_encode($encrypted);
    }
}
