<?php

namespace App\Helper;

use App\Models\SmsHistory;
use Illuminate\Support\Facades\Log;

class SmsHelper
{
    public static function historyStore($ownerUserId, $sid, $token, $from_number, $number, $message, $status, $error = null)
    {
        // Logging must NEVER break or retry a send. If the history insert fails (e.g. a schema
        // constraint), the SMS has already gone out — letting the exception propagate would fail
        // the queued SendSmsJob, which then RESENDS on retry (duplicate texts) and dead-letters.
        // So swallow any logging error here and just record it to the log channel.
        try {
            $history = new SmsHistory();
            $history->owner_user_id = $ownerUserId;
            $history->api = 'sid : ' . $sid . 'token : ' . $token . ' number : ' . $from_number;
            $history->phone_number = $number;
            $history->message = $message;
            $history->status = $status;
            $history->date = now();
            $history->error = $error;
            $history->save();
        } catch (\Throwable $e) {
            Log::channel('sms-mail')->error('SMS history log failed (send already attempted): ' . $e->getMessage());
        }
    }
}
