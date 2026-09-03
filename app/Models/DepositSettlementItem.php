<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One deduction line on a deposit settlement (arrears / damage / charge / other). */
class DepositSettlementItem extends Model
{
    use HasFactory;

    public const TYPE_ARREARS = 'arrears';
    public const TYPE_DAMAGE  = 'damage';
    public const TYPE_CHARGE  = 'charge';
    public const TYPE_OTHER   = 'other';

    protected $fillable = [
        'deposit_settlement_id', 'type', 'description', 'amount', 'invoice_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(DepositSettlement::class, 'deposit_settlement_id');
    }
}
