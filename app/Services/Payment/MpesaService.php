<?php


namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;
use Iankumu\Mpesa\Facades\Mpesa;

class MpesaService extends BasePaymentService
{

    public function __construct($method, $object)
    {
        parent::__construct($method, $object);
        
    }

    public function makePayment($paymentData)
    {
        $this->setAmount($paymentData['amount']);
        $mpesaAccount=$paymentData['mpesaAccount'];
        $amount = $this->amount;
        $phoneno = '0705075111';
        $account_number = 'TEST';
        $callbackurl=  $this->callbackUrl;

        $response = Mpesa::stkpush($phoneno, '1', $account_number, config('MPESA_CALLBACK_URL'));
        $result = json_decode((string)$response, true);

        // MpesaSTK::create([
        //     'merchant_request_id' =>  $result['MerchantRequestID'],
        //     'checkout_request_id' =>  $result['CheckoutRequestID']
        // ]);
    
        $data['success'] = false;
        $data['redirect_url'] = $callbackurl;
        $data['payment_id'] = '';
        $data['message'] = __(SOMETHING_WENT_WRONG);
        try {
            if ($result['ResponseCode']==DEACTIVATE) { 
                $data['merchant_request_id'] = $result['MerchantRequestID'];
                $data['checkout_request_id'] =  $result['CheckoutRequestID'];
                $data['payment_id'] = $result['CheckoutRequestID'];
                $data['success'] = true;
            }
            Log::info(json_encode($data));
            return $data;
        } catch (\Exception $ex) {
            return $data['message'] = $ex->getMessage();
        }
    }

    public function paymentConfirmation($checkout_id,$payer_id = null)
    {

        $data['success'] = false;
        $data['data'] = null;

        if ($checkout_id) {

            try{
                $response=Mpesa::stkquery($checkout_id);

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
