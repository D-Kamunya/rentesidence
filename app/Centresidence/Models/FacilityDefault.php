<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A facility default record (handbook §9.5.5). */
class FacilityDefault extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'defaulted_at'                      => 'datetime',
        'outstanding_principal_at_default'  => 'decimal:2',
        'outstanding_interest_at_default'   => 'decimal:2',
        'outstanding_penalty_at_default'    => 'decimal:2',
        'total_outstanding_at_default'      => 'decimal:2',
        'last_contact_date'                 => 'date',
        'next_action_date'                  => 'date',
        'resolved_at'                       => 'datetime',
        'recovery_amount'                   => 'decimal:2',
        'write_off_amount'                  => 'decimal:2',
    ];

    public const COLLECTIONS_INTERNAL = 'internal_collections';
    public const RESOLUTION_RESTRUCTURED = 'restructured';
    public const RESOLUTION_WRITTEN_OFF = 'written_off';

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(FinanceFacility::class, 'finance_facility_id');
    }
}
