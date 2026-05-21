<?php

namespace App\Services\SmsMail;

use App\Services\Sms\SmsCreditsService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\MpesaHelper;
use App\Helper\SmsHelper;
use App\Models\User;

class AdvantaSmsService
{
    use ResponseTrait;
    use MpesaHelper;

    public static function sendSms($numbers = [], $message = null, $ownerUserId = null)
    {
        $apikey    = getOption('ADVANTA_API_KEY');
        $partnerID = getOption('ADVANTA_PARTNER_ID');
        $shortcode = getOption('ADVANTA_SHORT_CODE');
    
        if (getOption('ADVANTA_STATUS', 0) != 1) {
            return __('SMS setting not enabled');
        }
    
        if (!count($numbers)) {
            return __('No number found');
        }
    
        // Credit gate is only active for owner-initiated sends.
        // null  → system/platform SMS (e.g. Centresidence → owner) — skip gate
        // admin → role check fails                                  — skip gate
        // owner → role check passes                                 — apply gate
        $creditCheckEnabled = false;
    
        if (!is_null($ownerUserId)) {
            $user               = User::find($ownerUserId);
            $creditCheckEnabled = $user && $user->role == USER_ROLE_OWNER;
        }
    
        $sentCount        = 0;
        $failedCount      = 0;
        $blockedByCredits = 0;
    
        foreach ($numbers as $number) {
    
            // ── Credit gate ───────────────────────────────────────
            if ($creditCheckEnabled) {
                $canSend = SmsCreditsService::deductOne(
                    $ownerUserId,
                    'SMS to ' . $number . ' — ' . mb_substr($message, 0, 60)
                );
    
                if (!$canSend) {
                    $blockedByCredits++;
                    SmsHelper::historyStore(
                        $ownerUserId, $shortcode, $apikey, $partnerID,
                        $number, $message, SMS_STATUS_FAILED,
                        'Insufficient SMS credits'
                    );
                    Log::channel('sms-mail')->warning(
                        "SMS blocked — insufficient credits for owner_user_id={$ownerUserId}, number={$number}"
                    );
                    continue;
                }
            }
    
            // ── Send ──────────────────────────────────────────────
            try {
                $payload = [
                    'apikey'    => $apikey,
                    'partnerID' => $partnerID,
                    'message'   => $message,
                    'shortcode' => $shortcode,
                    'mobile'    => (new self())->phoneValidator($number),
                ];
    
                $response = Http::post('https://quicksms.advantasms.com/api/services/sendsms/', $payload);
    
                if ($response->ok() && isset($response['responses'][0])) {
                    $responseData = $response['responses'][0];
    
                    if ($responseData['response-code'] == 200) {
                        Log::channel('sms-mail')->info('SMS sent successfully: ' . json_encode($responseData));
                        SmsHelper::historyStore($ownerUserId, $shortcode, $apikey, $partnerID, $number, $message, SMS_STATUS_DELIVERED);
                        $sentCount++;
                    } else {
                        throw new Exception('SMS failed: ' . $responseData['response-description']);
                    }
                } else {
                    throw new Exception('Unexpected API response: ' . $response->body());
                }
    
            } catch (Exception $e) {
                // Refund the credit if the send itself failed after deduction
                if ($creditCheckEnabled) {
                    SmsCreditsService::addCredits(
                        $ownerUserId, 1, 'refund', 0, '',
                        'Refund — send failed for ' . $number
                    );
                }
                SmsHelper::historyStore($ownerUserId, $shortcode, $apikey, $partnerID, $number, $message, SMS_STATUS_FAILED, $e->getMessage());
                Log::channel('sms-mail')->error('Error sending SMS: ' . $e->getMessage());
                $failedCount++;
            }
        }
    
        // ── Notify owner if any were blocked ─────────────────────
        if ($creditCheckEnabled && $blockedByCredits > 0) {
            SmsCreditsService::notifySendSummary($ownerUserId, $sentCount, $failedCount, $blockedByCredits);
        }
    
        return 'success';
    }
}