<?php


namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;
use Omnipay\Omnipay;

class PaypalService extends BasePaymentService
{
    public $omniPay;

    public function __construct($method, $object)
    {
        parent::__construct($method, $object);
        $this->omniPay = Omnipay::create('PayPal_Rest');
        $this->omniPay->setClientId($this->gateway->key);
        $this->omniPay->setSecret($this->gateway->secret);
        if ($this->gateway->mode == GATEWAY_MODE_SANDBOX) {
            $this->omniPay->setTestMode(true);
        } else {
            $this->omniPay->setTestMode(false);
        }
    }

    public function makePayment($paymentData)
    {
        $this->setAmount($paymentData['amount']);
        $response = $this->omniPay->purchase(array(
            'amount' => $this->amount,
            'currency' => $this->currency,
            'returnUrl' => $this->callbackUrl,
            'cancelUrl' => $this->callbackUrl,
        ))->send();
        Log::info('<<<<<$response->getData()>>>>>');
        Log::info($response->getData());
        $data['success'] = false;
        $data['redirect_url'] = '';
        $data['payment_id'] = '';
        $data['message'] = __(SOMETHING_WENT_WRONG);
        try {
            if ($response->isRedirect()) {
                $data['redirect_url'] = $response->getData()['links'][1]['href'];
                $data['payment_id'] = $response->getData()['id'];
                $data['success'] = true;
            }
            Log::info(json_encode($data));
            return $data;
        } catch (\Exception $ex) {
            return $data['message'] = $ex->getMessage();
        }
    }

    public function paymentConfirmation($payment_id, $payer_id = null)
    {

        $data['success'] = false;
        $data['data'] = null;

        if ($payment_id && $payer_id) {
            $transaction = $this->omniPay->completePurchase(array(
                'payer_id'             => $payer_id,
                'transactionReference' => $payment_id,
            ));
            $response = $transaction->send();

            if ($response->isSuccessful()) {
                $arr_body = $response->getData();
                Log::info($response->getData());
                $data['success'] = true;
                $data['data']['amount'] = $arr_body['transactions'][0]['amount']['total'];
                $data['data']['currency'] = $arr_body['transactions'][0]['amount']['currency'];
                $data['data']['payment_status'] = $arr_body['state'] == 'approved' ? 'success' : 'processing';
                $data['data']['payment_method'] = PAYPAL;
            }
        }
        return $data;
    }
}
