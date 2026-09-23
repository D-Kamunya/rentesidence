<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One tenant PERSON's aggregated rental payment-behaviour profile — the data home of the
 * Global Tenant ID. Keyed by identity_key (a canonical person key, normalised phone for now).
 * Written only by the screening aggregation engine (TenantCreditProfileService).
 */
class TenantCreditProfile extends Model
{
    protected $fillable = [
        'identity_key', 'phone', 'national_id', 'display_name',
        'tenancies_count', 'owners_count', 'landlord_rating_avg', 'ratings_count',
        'invoices_total', 'invoices_paid', 'on_time_count', 'late_count', 'overdue_count',
        'total_billed', 'total_paid', 'outstanding',
        'on_time_rate', 'avg_days_late',
        'score', 'score_band', 'score_grade', 'score_version', 'is_thin_file', 'score_factors',
        'first_activity_at', 'last_activity_at', 'computed_at',
        'activated_at', 'activated_by_user_id',
    ];

    protected $casts = [
        'total_billed'      => 'decimal:2',
        'total_paid'        => 'decimal:2',
        'outstanding'       => 'decimal:2',
        'on_time_rate'      => 'decimal:2',
        'avg_days_late'     => 'decimal:2',
        'score'             => 'decimal:2',
        'is_thin_file'      => 'boolean',
        'score_factors'     => 'array',
        'first_activity_at' => 'datetime',
        'last_activity_at'  => 'datetime',
        'computed_at'       => 'datetime',
        'activated_at'      => 'datetime',
    ];

    public function disputes()
    {
        return $this->hasMany(TenantCreditDispute::class);
    }
}
