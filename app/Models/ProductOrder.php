<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str; 

class ProductOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id', 
        'payment_id',
        'transaction_id',
        'mpesa_transaction_code',
        'user_id',
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
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatically generate order_id before creating the model
        static::creating(function ($model) {
            $model->order_id = 'ORDERID' . strtoupper(Str::random(8));
        });
    }

    // product_orders.order_status
    public function scopeOrderPending($query)
    {
        return $query->where('order_status', ORDER_STATUS_PENDING);
    }

    public function scopeOrderCompleted($query)
    {
        return $query->where('order_status', ORDER_STATUS_COMPLETED);
    }

    public function scopeOrderCancelled($query)
    {
        return $query->where('order_status', ORDER_STATUS_CANCELLED);
    }
    public function scopeRefundPending($query)
    {
        return $query->where('payment_status', PRODUCT_ORDER_STATUS_REFUND_PENDING);
    }

    // product_orders.payment_status
    public function scopePaymentPending($query)
    {
        return $query->where('payment_status', PRODUCT_ORDER_STATUS_PENDING);
    }

    public function scopePaymentPaid($query)
    {
        return $query->where('payment_status', PRODUCT_ORDER_STATUS_PAID);
    }

    public function scopePaymentCancelled($query)
    {
        return $query->where('payment_status', PRODUCT_ORDER_STATUS_CANCELLED);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function gateway(): HasOne
    {
        return $this->hasOne(Gateway::class, 'id', 'gateway_id');
    }

    public function orderItems()
    {
        return $this->hasMany(ProductOrderItem::class, 'product_order_id');
    }
}
