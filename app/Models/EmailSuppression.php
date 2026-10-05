<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An address the platform will not send to (deliverability safety net). See EmailGuard.
 */
class EmailSuppression extends Model
{
    protected $fillable = ['email', 'reason', 'source'];
}
