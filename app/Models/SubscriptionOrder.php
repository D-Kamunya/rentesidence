<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\AffiliateCommissionService;
use Illuminate\Support\Facades\Log;
use App\Models\OwnerPackage;

class SubscriptionOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_id',
        'transaction_id',
        'user_id',
        'package_id',
        'duration_type',
        'amount',
        'tax_amount',
        'tax_percentage',
        'system_currency',
        'gateway_id',
        'gateway_currency',
        'conversion_rate',
        'subtotal',
        'total',
        'transaction_amount',
        'payment_status',
        'bank_id',
        'bank_name',
        'bank_account_number',
        'deposit_by',
        'deposit_slip_id',
        'quantity',
        'package_type',
        'mpesa_transaction_code'
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function package()
    {
        return $this->belongsTo(OwnerPackage::class, 'package_id');
    }
    
    protected static function booted()
    {
        static::created(function ($order) {
            Log::info('Subscription order created: ' . $order->id);
            app(AffiliateCommissionService::class)->handleSubscriptionPayment($order);
        });

        static::updated(function ($order) {
            // Optional: only recalc if payment status or amount changes
            if ($order->wasChanged(['payment_status'])) {
                Log::info('Subscription order updated: ' . $order->id);
                app(AffiliateCommissionService::class)->handleSubscriptionPayment($order);
            }
        });
    }
}
