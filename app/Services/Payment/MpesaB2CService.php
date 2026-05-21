<?php

namespace App\Services\Payment;

class MpesaB2CService
{
    use MpesaHelper;

    public function __construct()
    {
        $this->url = config('mpesa.environment') === 'sandbox'
            ? 'https://sandbox.safaricom.co.ke'
            : 'https://api.safaricom.co.ke';

        $this->consumer_key    = config('mpesa.mpesa_consumer_key');
        $this->consumer_secret = config('mpesa.mpesa_consumer_secret');
    }

    public function send(string $phone, float $amount): array
    {
        $payload = [
            'InitiatorName'      => config('mpesa.initiator_name'),
            'SecurityCredential' => $this->encryptInitiatorPassword(config('mpesa.initiator_password')),
            'CommandID'          => 'BusinessPayment',
            'Amount'             => (int) round($amount),
            'PartyA'             => config('mpesa.b2c_shortcode') ?: config('mpesa.shortcode'),
            'PartyB'             => $this->phoneValidator($phone),
            'Remarks'            => 'Owner wallet withdrawal',
            'QueueTimeOutURL'    => config('mpesa.b2c_timeout_url'),
            'ResultURL'          => config('mpesa.b2c_result_url'),
            'Occasion'           => 'OwnerWithdrawal',
        ];

        $response = $this->MpesaRequest($this->url . '/mpesa/b2c/v1/paymentrequest', $payload);

        $result  = $response->json() ?? [];
        $success = $response->successful()
            && isset($result['ResponseCode'])
            && (string) $result['ResponseCode'] === '0';

        return [
            'success'   => $success,
            'message'   => $result['ResponseDescription']
                ?? $result['errorMessage']
                ?? $response->body(),
            'reference' => $result['ConversationID']
                ?? $result['OriginatorConversationID']
                ?? null,
            'response'  => $result,
        ];
    }

    private function encryptInitiatorPassword(string $plainPassword): string
    {
        $certPath = config('mpesa.environment') === 'sandbox'
            ? storage_path('app/mpesa/sandbox.cer')
            : storage_path('app/mpesa/production.cer');
    
        if (!file_exists($certPath)) {
            throw new \Exception("B2C certificate not found at: {$certPath}");
        }
    
        $cert   = file_get_contents($certPath);
        $pubKey = openssl_get_publickey($cert);
    
        if (!$pubKey) {
            throw new \Exception("B2C certificate could not be parsed at: {$certPath}");
        }
    
        openssl_public_encrypt($plainPassword, $encrypted, $pubKey, OPENSSL_PKCS1_PADDING);
    
        return base64_encode($encrypted);
    }

}
