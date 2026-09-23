<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Aggregated payout to a partner — the M-Pesa B2C seam (handbook §9.5.4). */
class PartnerRemittanceBatch extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'remittance_date'          => 'date',
        'total_amount'             => 'decimal:2',
        'gross_amount'             => 'decimal:2',
        'servicing_fee'            => 'decimal:2',
        'origination_fee'          => 'decimal:2',
        'net_amount'               => 'decimal:2',
        'sent_at'                  => 'datetime',
        'confirmation_received_at' => 'datetime',
    ];

    public const STATUS_PREPARED  = 'prepared';
    public const STATUS_SENT      = 'sent';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_FAILED    = 'failed';

    public function partner(): BelongsTo
    {
        return $this->belongsTo(FinancePartner::class, 'finance_partner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PartnerRemittanceBatchItem::class);
    }
}
