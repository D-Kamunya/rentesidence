<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SubscriptionOrder;

class AffiliateCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id','owner_id','subscription_id','subscription_payment_id',
        'subscription_amount','type','period_month','period_year'
    ];


    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function subscription()
    {
        return $this->belongsTo(SubscriptionOrder::class, 'subscription_id');
    }
}
