<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A phone number the platform will not send SMS to (deliverability safety net). The SMS sibling of
 * EmailSuppression — see SmsGuard. Numbers are stored normalised (e.g. 254700000101).
 */
class SmsSuppression extends Model
{
    protected $fillable = ['number', 'reason', 'source'];
}
