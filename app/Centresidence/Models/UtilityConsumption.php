<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A utility consumption (drawdown) event against a wallet (handbook Token
 * Engine: usage deduction).
 */
class UtilityConsumption extends Model
{
    use HasFactory;

    protected $table = 'utility_consumption';

    protected $guarded = [];

    protected $casts = [
        'units_consumed' => 'decimal:4',
        'balance_after'  => 'decimal:4',
        'recorded_at'    => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(UtilityWallet::class, 'utility_wallet_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
