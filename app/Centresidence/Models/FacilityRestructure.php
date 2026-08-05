<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** New terms after a default (handbook §9.5.5). */
class FacilityRestructure extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'new_interest_rate'        => 'decimal:2',
        'new_monthly_target'       => 'decimal:2',
        'new_deduction_percentage' => 'decimal:2',
        'new_maturity_date'        => 'date',
        'restructure_fee'          => 'decimal:2',
        'approved_by_partner'      => 'boolean',
        'approved_by_owner'        => 'boolean',
        'effective_date'           => 'date',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(FinanceFacility::class, 'finance_facility_id');
    }

    public function default(): BelongsTo
    {
        return $this->belongsTo(FacilityDefault::class, 'facility_default_id');
    }
}
