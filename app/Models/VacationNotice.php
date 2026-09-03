<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A tenant's notice to vacate. See the migration for the enforce-as-default model (early move-out
 * allowed but flagged via meets_notice). Anchors the Phase-4 final invoice + deposit settlement.
 */
class VacationNotice extends Model
{
    use HasFactory;

    public const STATUS_PENDING      = 'pending';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';
    public const STATUS_WITHDRAWN    = 'withdrawn';
    public const STATUS_COMPLETED    = 'completed';

    /** Statuses that mean the notice is still LIVE (used for the "one active notice per tenancy" guard). */
    public const ACTIVE_STATUSES = [self::STATUS_PENDING, self::STATUS_ACKNOWLEDGED];

    protected $fillable = [
        'tenant_id', 'owner_user_id', 'property_id', 'property_unit_id',
        'notice_date', 'intended_move_out_date', 'notice_period_days',
        'meets_notice', 'message', 'status', 'acknowledged_at',
    ];

    protected $casts = [
        'notice_date'            => 'date',
        'intended_move_out_date' => 'date',
        'meets_notice'           => 'boolean',
        'acknowledged_at'        => 'datetime',
    ];

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'property_unit_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
