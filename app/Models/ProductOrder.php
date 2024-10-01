<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
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
        'payment_status', // Adjust as necessary
        'bank_id',
        'bank_name',
        'bank_account_number',
        'deposit_by',
        'deposit_slip_id',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(ProductOrderItem::class);
    }
}
