<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An individual deduction event (handbook §9.5.3). Append-only. */
class SettlementTransaction extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'amount'    => 'decimal:2',
        'settled_at' => 'datetime',
    ];

    public const BENEFICIARY_PARTNER      = 'finance_partner';
    public const BENEFICIARY_CENTRESIDENCE = 'centresidence';

    public const RECON_PENDING    = 'pending';
    public const RECON_RECONCILED = 'reconciled';

    public function scopePendingForPartner($query, int $partnerId)
    {
        return $query->where('beneficiary_type', self::BENEFICIARY_PARTNER)
            ->where('beneficiary_id', $partnerId)
            ->where('reconciliation_status', self::RECON_PENDING);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(FinanceFacility::class, 'finance_facility_id');
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(SettlementCycle::class, 'settlement_cycle_id');
    }
}
