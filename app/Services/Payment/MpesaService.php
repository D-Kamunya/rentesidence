<?php


namespace App\Services\Payment;

use App\Services\Payment\MpesaHelper;

class MpesaService extends BasePaymentService
{
    
    use MpesaHelper;
    public $url;
    public $consumer_key;
    public $consumer_secret;
    public $shortcode;
    public $passkey;
    public $stkcallback;
    public $payment;

    public function __construct($method, $object)
    {
        parent::__construct($method, $object);
        $this->url = config('mpesa.environment') == 'sandbox'
            ? 'https://sandbox.safaricom.co.ke'
            : 'https://api.safaricom.co.ke';
        $this->payment = $object;
        $this->consumer_key = config('mpesa.mpesa_consumer_key');
        $this->consumer_secret = config('mpesa.mpesa_consumer_secret');
        $this->shortcode = config('mpesa.shortcode');
        $this->passkey = config('mpesa.passkey');
        $this->stkcallback = config('mpesa.callback_url');
        
    }

    public function makePayment($paymentData)
    {
        $customerPhoneNumber = $paymentData['phone'] 
            ?? $paymentData['mpesaNumber'] 
            ?? auth()->user()->contact_number;

        $this->setAmount($paymentData['amount']);
        $mpesaAccount    = $paymentData['mpesaAccount'];
        $amount          = $this->amount;
        // TransactionDesc is a short, restricted Safaricom field (≤13 chars, alphanumeric).
        // A raw routing type like "credit:agreement" (colon and >13 chars) can make Daraja
        // ACCEPT the request yet never deliver the STK prompt. Sanitize to a safe form for
        // the push only — the callback routing below still uses the untouched payment['type'].
        $transaction_desc = substr(preg_replace('/[^A-Za-z0-9 ]/', '', (string) $this->payment['type']), 0, 13) ?: 'Payment';
        $callbackurl     = $this->callbackUrl;

        $callbackWithParams = config('mpesa.callback_url')
            . '?type=' . urlencode($this->payment['type'])
            . '&id='   . urlencode($this->payment['id']);

        $response = $this->stkpush(
            $customerPhoneNumber,
            $amount,
            $mpesaAccount,
            $transaction_desc,
            $callbackWithParams
        );

        $result = json_decode((string)$response, true);

        $data['success']      = false;
        $data['redirect_url'] = $callbackurl;
        $data['payment_id']   = '';
        $data['message']      = __(SOMETHING_WENT_WRONG);

        try {
            if (isset($result['ResponseCode']) && $result['ResponseCode'] == DEACTIVATE) {
                $data['merchant_request_id'] = $result['MerchantRequestID'];
                $data['checkout_request_id'] = $result['CheckoutRequestID'];
                $data['payment_id']          = $result['CheckoutRequestID'];
                $data['success']             = true;
            } elseif (isset($result['errorMessage'])) {
                $data['message'] = __($result['errorMessage']);
            }
            return $data;
        } catch (\Exception $ex) {
            $data['message'] = $ex->getMessage();
            return $data;
        }
    }

    public function paymentConfirmation($checkout_id,$payer_id = null)
    {

        $data['success'] = false;
        $data['data'] = null;

        if ($checkout_id) {

            try{
                $response=$this->stkquery($checkout_id);

                $result = json_decode((string)$response);
                if ($result->ResultCode == DEACTIVATE) {
                    $data['success'] = true;
                    $data['data']['payment_status'] = 'success';
                    $data['data']['payment_method'] = MPESA;
                }elseif ($result->ResultCode != DEACTIVATE && isset($result->ResultDesc)) {
                    $data['data']['error'] = $result->ResultDesc;
                }
            
                return $data;
            }catch(\Exception $e) {
                $data['data']['error'] = $e->getMessage();
                return $data;
            }
        }
        return $data;
    }

}
