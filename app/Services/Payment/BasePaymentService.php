<?php


namespace App\Services\Payment;

use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\User;

class BasePaymentService
{
    public $paymentMethod;
    public $callbackUrl;
    public $currency;
    public $gateway;
    public $gatewayCurrency;
    public $amount;
    public $type;

    public function __construct($method, $object)
    {
        if (isset($object['id'])) {
            $this->callbackUrl = $object['callback_url'] . '?id=' . $object['id'];
            $newCallbackUrl = env('MPESA_CALLBACK_URL') . '?id=' . $object['id'];
            config(['MPESA_CALLBACK_URL' => $newCallbackUrl]);
        }

        if (isset($object['gateway'])) {
            $this->callbackUrl = $object['callback_url'] . '?id=' . $object['id'] . '&gateway=' . $object['gateway'];
        }

        if (isset($object['currency'])) {
            $this->currency = $object['currency'];
        }
        if (isset($object['type'])) {
            $this->type = $object['type'];
        }

        $this->paymentMethod = $method;
        if ($this->type == 'subscription') {
            $userId = User::where('role', USER_ROLE_ADMIN)->first()->id;
        } else {
            $userId = auth()->user()->owner_user_id;
        }
        $this->gateway = Gateway::where(['owner_user_id' => $userId, 'slug' => $this->paymentMethod])->first();
        $this->gatewayCurrency = GatewayCurrency::where(['owner_user_id' => $userId, 'gateway_id' => $this->gateway->id, 'currency' => $this->currency])->firstOrFail();
    }

    public function calculateAmount($amount)
    {
        return $this->numberParser($this->gatewayCurrency->conversion_rate) * $this->numberParser($amount);
    }

    public function setAmount($amount)
    {
        $this->amount = $this->calculateAmount($amount);
    }

    function numberParser($value)
    {
        return (float) str_replace(',', '', number_format(($value), 2));
    }
}
