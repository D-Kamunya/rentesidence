<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id','owner_id','subscription_id','subscription_payment_id',
        'subscription_amount','type','period_month','period_year'
    ];
}
