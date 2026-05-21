<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'owner_wallet_id',
        'product_order_id',
        'invoice_order_id',
        'transaction_source',
        'gross_amount',
        'commission_rate',
        'commission_amount',
        'net_amount',
        'type',
        'description',
    ];

    protected $casts = [
        'gross_amount'      => 'float',
        'commission_rate'   => 'float',
        'commission_amount' => 'float',
        'net_amount'        => 'float',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function wallet()
    {
        return $this->belongsTo(OwnerWallet::class, 'owner_wallet_id');
    }

    public function productOrder()
    {
        return $this->belongsTo(ProductOrder::class, 'product_order_id');
    }

    public function invoiceOrder()
    {
        return $this->belongsTo(Order::class, 'invoice_order_id');
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeMarketplace($query)
    {
        return $query->where('transaction_source', 'marketplace');
    }

    public function scopeRent($query)
    {
        return $query->where('transaction_source', 'rent');
    }
}