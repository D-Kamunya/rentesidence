<?php

namespace App\Services\SmsMail;

use App\Models\SmsHistory;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\MpesaHelper;
use App\Helper\SmsHelper;

class AdvantaSmsService
{
    use ResponseTrait;
    use MpesaHelper;

    public static function sendSms($numbers = [], $message = null, $ownerUserId = null)
    {
        // Retrieve the Advanta API credentials and settings
        $apikey = getOption('ADVANTA_API_KEY');
        $partnerID = getOption('ADVANTA_PARTNER_ID');
        $shortcode = getOption('ADVANTA_SHORT_CODE');

        // Check if the Advanta SMS service is enabled
        if (getOption('ADVANTA_STATUS', 0) == 1) {
            if (count($numbers)) {
                foreach ($numbers as $key => $number) {
                    try {
                        // Prepare the request payload
                        $payload = [
                            "apikey" => $apikey,
                            "partnerID" => $partnerID,
                            "message" => $message,
                            "shortcode" => $shortcode,
                            "mobile" =>  (new self())->phoneValidator($number),
                        ];
                        // Send the POST request to Advanta SMS API
                        $response = Http::post('https://quicksms.advantasms.com/api/services/sendsms/', $payload);

                        // Check the response status
                        if ($response->ok() && isset($response['responses'][0])) {
                            $responseData = $response['responses'][0];

                            // Log and store SMS history if the response code indicates success
                            if ($responseData['response-code'] == 200) {
                                Log::channel('sms-mail')->info('SMS sent successfully: ' . json_encode($responseData));
                                SmsHelper::historyStore($ownerUserId, $shortcode, $apikey, $partnerID, $number, $message, SMS_STATUS_DELIVERED);
                            } else {
                                // Handle failed SMS delivery
                                throw new Exception('SMS failed: ' . $responseData['response-description']);
                            }
                        } else {
                            // Handle unexpected or failed API responses
                            throw new Exception('Unexpected API response: ' . $response->body());
                        }
                    } catch (Exception $e) {
                        // Store the failure in the SMS history and log the error
                        SmsHelper::historyStore($ownerUserId, $shortcode, $apikey, $partnerID, $number, $message, SMS_STATUS_FAILED, $e->getMessage());
                        Log::channel('sms-mail')->error('Error sending SMS: ' . $e->getMessage());
                    }
                }
                return 'success';
            } else {
                return __('No number found');
            }
        } else {
            return __('SMS setting not enabled');
        }
    }
}
