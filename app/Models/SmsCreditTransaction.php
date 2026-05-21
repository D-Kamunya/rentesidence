<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCreditTransaction extends Model
{
    protected $fillable = [
        'owner_user_id',
        'type',
        'quantity',
        'amount_paid',
        'balance_before',
        'balance_after',
        'reference',
        'description',
        'status',
    ];

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Owner::class, 'owner_user_id', 'user_id');
    }
}