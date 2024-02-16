<?php


namespace App\Services\Payment;

use App\Models\Gateway;
use Illuminate\Support\Facades\Log;

class MercadoPagoService extends BasePaymentService
{
    private $orderId;
    private $client_id;
    private $client_secret;
    private $test_mode;

    public function __construct($method, $object)
    {
        parent::__construct($method, $object);
        if (isset($object['id'])) {
            $this->orderId = $object['id'];
        }

        $this->client_id = $this->gateway->key;
        $this->client_secret = $this->gateway->secret;
        $this->test_mode = ($this->gateway->mode == GATEWAY_MODE_SANDBOX) ? true : false;
    }

    protected function setAccessToken()
    {
        return \MercadoPago\SDK::setAccessToken($this->client_secret);
    }

    public function makePayment($amount)
    {
        $this->setAmount($amount);
        $data['success'] = false;
        $data['redirect_url'] = '';
        $data['payment_id'] = '';
        $data['message'] = SOMETHING_WENT_WRONG;
        try {
            $order_id =  $this->orderId;
            $this->verify_currency();
            $this->setAccessToken();
            $preference = new \MercadoPago\Preference();

            $item = new \MercadoPago\Item();
            $item->id = $order_id;
            $item->title = "";
            $item->quantity = 1;
            $item->unit_price = $this->amount;

            $preference->items = array($item);

            $preference->back_urls = array(
                "success" => $this->callbackUrl,
                "failure" => $this->callbackUrl,
                "pending" => $this->callbackUrl
            );
            $preference->auto_return = "approved";
            $preference->metadata = array(
                "order_id" => $order_id,
            );

            $preference->save();
            $data['redirect_url'] = $preference->init_point;
            $data['payment_id'] = $preference->id;
            $data['success'] = true;
            return $data;
        } catch (\Exception $ex) {
            Log::info($ex);
            $data['message'] = $ex->getMessage();
            return $data;
        }
    }

    public function paymentConfirmation($payment_id)
    {

        $data['success'] = false;
        $data['data'] = null;
        $this->setAccessToken();

        $payment = \MercadoPago\Payment::find_by_id($payment_id);

        if (!is_null($payment) && $payment->status == 'approved') {
            $data['success'] = true;
            $data['data']['amount'] = $payment->transaction_amount;
            $data['data']['currency'] = $this->currency;
            $data['data']['payment_status'] =  'success';
            $data['data']['payment_method'] = MERCADOPAGO;
        } else {
            $data['success'] = false;
            $data['data']['currency'] = $this->currency;
            $data['data']['payment_status'] =  'unpaid';
            $data['data']['payment_method'] = MERCADOPAGO;
        }

        return $data;
    }

    public function verify_currency()
    {
        if (in_array($this->currency, $this->supported_currency_list(), true)) {
            return true;
        }
        return throw new \Exception($this->currency . __(' is not supported by ' . $this->gateway_name()));
    }

    public function supported_currency_list()
    {
        return ['BRL'];
    }


    public function gateway_name()
    {
        return 'mercadopago';
    }
}
