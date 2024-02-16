<?php

namespace App\Services\Payment;

use Stripe\StripeClient;

class StripeService extends BasePaymentService
{
    public  $stripClient;

    public function __construct($method, $object)
    {
        parent::__construct($method, $object);
        $this->stripClient = new StripeClient($this->gateway->key);
    }

    public function makePayment($amount)
    {
        $this->setAmount($amount);
        $data['success'] = false;
        $data['redirect_url'] = '';
        $data['payment_id'] = '';
        $data['message'] = SOMETHING_WENT_WRONG;

        $payment = $this->stripClient->checkout->sessions->create([
            'success_url' => $this->callbackUrl,
            'cancel_url' => $this->callbackUrl,
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $this->currency,
                        'product_data' => [
                            'name' => 'Amount',
                        ],
                        'unit_amount' => $this->amount * 100,
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
        ]);

        try {

            if ($payment->status == 'open') {
                $data['payment_id'] = $payment->payment_intent;
                $data['success'] = true;
                $data['redirect_url'] = $payment->url;
            }

            return $data;
        } catch (\Exception $ex) {
            return $data['message'] = $ex->getMessage();
        }
        return $data;
    }

    public function paymentConfirmation($payment_id)
    {
        $data['data'] = null;
        $payment = $this->stripClient->paymentIntents->retrieve($payment_id, []);
        if ($payment->status == 'succeeded') {
            $data['success'] = true;
            $data['data']['amount'] = $payment->amount_received;
            $data['data']['currency'] = $payment->currency;
            $data['data']['payment_status'] =  'success';
            $data['data']['payment_method'] = STRIPE;
        } else {
            $data['success'] = false;
            $data['data']['amount'] = $payment->amount;
            $data['data']['currency'] = $payment->currency;
            $data['data']['payment_status'] =  'unpaid';
            $data['data']['payment_method'] = STRIPE;
        }

        return $data;
    }
}
