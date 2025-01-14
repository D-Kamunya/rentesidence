<?php

namespace App\Services\Payment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

trait MpesaHelper
{

    public $url;
    public $consumer_key;
    public $consumer_secret;
    public $shortcode;
    public $passkey;
    public $stkcallback;

    public function __construct()
    {
        $this->url = config('mpesa.environment') == 'sandbox'
            ? 'https://sandbox.safaricom.co.ke'
            : 'https://api.safaricom.co.ke';

        $this->consumer_key = config('mpesa.mpesa_consumer_key');
        $this->consumer_secret = config('mpesa.mpesa_consumer_secret');
        $this->shortcode = config('mpesa.shortcode');
        $this->passkey = config('mpesa.passkey');
        $this->stkcallback = config('mpesa.callback_url');
    }

    public function confirm (Request $request){
        Log::info($request->getContent());
        error_log($request->all());
    }

     // Generate an AccessToken using the Consumer Key and Consumer Secret
    public function generateAccessToken()
    {
        $consumer_key = config('mpesa.mpesa_consumer_key');
        $consumer_secret =config('mpesa.mpesa_consumer_secret');
        
        $url = $this->url . '/oauth/v1/generate?grant_type=client_credentials';

        $response = Http::withBasicAuth($consumer_key, $consumer_secret)
            ->get($url);

        $result = json_decode($response);
        return data_get($result, 'access_token');
    }

    public function LipaNaMpesaPassword()
    {
        $timestamp = Carbon::rawParse('now')->format('YmdHis');

        return base64_encode($this->shortcode . $this->passkey . $timestamp);
    }

    public function stkpush($phonenumber, $amount, $mpesa_account, $transaction_desc, $callbackurl = null)
    {
        $url = $this->url . '/mpesa/stkpush/v1/processrequest';
        $transactionType = $mpesa_account->account_type == 'PAYBILL' ? 'CustomerPayBillOnline' : 'CustomerBuyGoodsOnline';
        $partyB = $mpesa_account->account_type == 'PAYBILL' ? $mpesa_account->paybill : $mpesa_account->till_number;
        $accountReference = $mpesa_account->account_type == 'PAYBILL' ? $mpesa_account->account_name : 'CENTRESIDENCE PROPERTY MANAGEMENT';
        $data = [
            'BusinessShortCode' => $this->shortcode, 
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHis'),
            'Amount' => (int) $amount,
            'PhoneNumber' => $this->phoneValidator($phonenumber),
            'PartyA' => $this->phoneValidator($phonenumber),
            'TransactionDesc' => $transaction_desc, 
            'TransactionType' => $transactionType,
            'PartyB' =>$partyB,
            'AccountReference' =>$accountReference, 
        ];

        if (!is_null($callbackurl) && is_null($this->stkcallback)) {
            $data += [
                'CallBackURL' => $callbackurl,
            ];
        } elseif (is_null($callbackurl) && !is_null($this->stkcallback)) {
            $data += [
                'CallBackURL' => $this->stkcallback,
            ];
        } elseif (!is_null($callbackurl) && !is_null($this->stkcallback)) {
            $data += [
                'CallBackURL' => $callbackurl,
            ];
        } else {
            throw CallbackException::make(
                'callback_url',
                'Ensure you have set a Callback URL in the mpesa config file'
            );
        }

        return $this->MpesaRequest($url, $data);
    }

    public function MpesaRequest($url, $body)
    {

        $response = Http::withToken($this->generateAccessToken())
            ->acceptJson()
            ->post($url, $body);
        return $response;
    }

    public function phoneValidator($phoneno)
    {
        // Some validations for the phonenumber to format it to the required format
        $phoneno = (substr($phoneno, 0, 1) == '+') ? str_replace('+', '', $phoneno) : $phoneno;
        $phoneno = (substr($phoneno, 0, 1) == '0') ? preg_replace('/^0/', '254', $phoneno) : $phoneno;
        $phoneno = (substr($phoneno, 0, 1) == '7') ? "254{$phoneno}" : $phoneno;
        return $phoneno;
    }

    public function stkquery($checkoutRequestId)
    {
        $post_data = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHis'),
            'CheckoutRequestID' => $checkoutRequestId,
        ];

        $url = $this->url . '/mpesa/stkpushquery/v1/query';

        return $this->MpesaRequest($url, $post_data);
    }
}
