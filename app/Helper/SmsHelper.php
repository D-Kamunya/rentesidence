<?php

namespace App\Helper;

use App\Models\SmsHistory;

class SmsHelper
{
    public static function historyStore($ownerUserId, $sid, $token, $from_number, $number, $message, $status, $error = null)
    {
        $history = new SmsHistory();
        $history->owner_user_id = $ownerUserId;
        $history->api = 'sid : ' . $sid . 'token : ' . $token . ' number : ' . $from_number;
        $history->phone_number = $number;
        $history->message = $message;
        $history->status = $status;
        $history->date = now();
        $history->error = $error;
        $history->save();
    }
}
