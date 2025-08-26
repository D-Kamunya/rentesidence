<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateCommissionPayment extends Model
{
    use HasFactory;
     protected $fillable = [
        'affiliate_id','period_month','period_year',
        'total_new_clients','total_recurring_clients',
        'new_commissions_amount','recurring_commissions_amount',
        'new_commission_payout','recurring_commission_payout','total_commission_payout'
    ];
}
