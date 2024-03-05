<?php


namespace App\Services\Payment;

class Payment
{
    public  $provider = null;
    public function __construct($method, $object = [])
    {
        $classPath = getPaymentServiceClass($method);
        $this->provider = new $classPath($method, $object);
    }

    public function makePayment($paymentData)
    {
        $res = $this->provider->makePayment($paymentData);
        return $res;
    }

    public function paymentConfirmation($payment_id, $payer_id = null)
    {
        if (is_null($payer_id)) {
            return $this->provider->paymentConfirmation($payment_id);
        }
        return $this->provider->paymentConfirmation($payment_id, $payer_id);
    }
}
