<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Links a settlement transaction to a remittance batch (handbook §9.5.4). */
class PartnerRemittanceBatchItem extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PartnerRemittanceBatch::class, 'partner_remittance_batch_id');
    }

    public function settlementTransaction(): BelongsTo
    {
        return $this->belongsTo(SettlementTransaction::class, 'settlement_transaction_id');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(FinanceFacility::class, 'facility_id');
    }
}
