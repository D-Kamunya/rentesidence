<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    /** The affiliate who referred this enquiry (via ?referral=CODE), if any. FK = affiliates.id. */
    public function referralAffiliate()
    {
        return $this->belongsTo(Affiliate::class, 'referral_affiliate_id');
    }
}
