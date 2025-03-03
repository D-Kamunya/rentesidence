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
        $customerPhoneNumber=auth()->user()->contact_number;
        $this->setAmount($paymentData['amount']);
        $mpesaAccount=$paymentData['mpesaAccount'];
        $amount = $this->amount;
        $transaction_type=$this->payment['type'];
        $callbackurl=  $this->callbackUrl;
        $response = $this->stkpush($customerPhoneNumber, $amount, $mpesaAccount,$transaction_type, config('MPESA_CALLBACK_URL'));
        $result = json_decode((string)$response, true);

        $data['success'] = false;
        $data['redirect_url'] = $callbackurl;
        $data['payment_id'] = '';
        $data['message'] = __(SOMETHING_WENT_WRONG);
        try {
            if (isset($result['ResponseCode']) && $result['ResponseCode']==DEACTIVATE) { 
                $data['merchant_request_id'] = $result['MerchantRequestID'];
                $data['checkout_request_id'] =  $result['CheckoutRequestID'];
                $data['payment_id'] = $result['CheckoutRequestID'];
                $data['success'] = true;
            }elseif (isset($result['errorMessage'])) {
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
                }
            
                return $data;
            }catch(\Exception $e) {
                return $data;
            }
        }
        return $data;
    }
}
