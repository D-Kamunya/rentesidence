<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Unified ledger of an owner's prepaid-credit movements across every bucket (SMS,
 * agreement, …). Each row is tagged with `bucket`; the shared money rail
 * (App\Services\Credit\CreditService) is the only writer of purchase/deduct rows.
 */
class OwnerCreditTransaction extends Model
{
    protected $fillable = [
        'bucket',
        'owner_user_id',
        'type',
        'quantity',
        'amount_paid',
        'balance_before',
        'balance_after',
        'reference',
        'payment_id',
        'description',
        'status',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class, 'owner_user_id', 'user_id');
    }

    /** Scope to a single bucket, e.g. OwnerCreditTransaction::bucket('sms'). */
    public function scopeBucket($query, string $bucket)
    {
        return $query->where('bucket', $bucket);
    }
}
