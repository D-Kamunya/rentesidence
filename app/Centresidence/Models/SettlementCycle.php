<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A collection/remittance window per facility+partner (handbook §9.5.2). */
class SettlementCycle extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'cycle_start'      => 'date',
        'cycle_end'        => 'date',
        'expected_amount'  => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'remitted_amount'  => 'decimal:2',
        'remittance_date'  => 'date',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(FinanceFacility::class, 'finance_facility_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(FinancePartner::class, 'finance_partner_id');
    }
}
