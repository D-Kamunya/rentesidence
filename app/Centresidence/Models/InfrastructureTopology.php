<?php

namespace App\Centresidence\Models;

use App\Centresidence\Support\Money;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One owner's share of one infrastructure asset for one property — the billing
 * source of truth (handbook §4.2). The Infrastructure Cost Job (WP3) iterates
 * active rows and turns each into invoice line items.
 *
 * Backed by the `infrastructure_topology` table.
 */
class InfrastructureTopology extends Model
{
    use HasFactory;

    protected $table = 'infrastructure_topology';

    protected $guarded = [];

    protected $casts = [
        'allocation_percentage' => 'decimal:2',
        'monthly_base_cost'     => 'decimal:2',
        'cost_per_device_max'   => 'decimal:2',
        'effective_from'        => 'date',
        'effective_to'          => 'date',
    ];

    public const TYPE_GATEWAY        = 'gateway';
    public const TYPE_NETWORK_SERVER = 'network_server';
    public const TYPE_MESH_NODE      = 'mesh_node';

    public const STATUS_ACTIVE      = 'active';
    public const STATUS_INACTIVE    = 'inactive';
    public const STATUS_MAINTENANCE = 'maintenance';

    public const BILLING_FLAT_MONTHLY             = 'flat_monthly';
    public const BILLING_PER_DEVICE_CAPPED        = 'per_active_device_capped';
    public const BILLING_PER_DEVICE_UNCAPPED      = 'per_active_device_uncapped';

    /** Map polymorphic type → model class so the engine stays asset-agnostic. */
    public const ASSET_MAP = [
        self::TYPE_GATEWAY => Gateway::class,
    ];

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /** Rows in effect on a given date (effective_from..effective_to window). */
    public function scopeEffectiveOn(Builder $query, $date): Builder
    {
        return $query
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date);
            });
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class, 'infrastructure_id');
    }

    /** Resolve the underlying asset model regardless of type. */
    public function asset(): ?Model
    {
        $class = self::ASSET_MAP[$this->infrastructure_type] ?? null;

        return $class ? $class::find($this->infrastructure_id) : null;
    }

    // ── Cost allocation (handbook §4.3) ───────────────────────────────────

    /**
     * This owner's share of the asset's monthly cost:
     *   owner_infrastructure_share = monthly_base_cost × allocation_percentage / 100
     *
     * Scenario B: KES 10,000 gateway, 60% → KES 6,000; 40% → KES 4,000.
     */
    public function ownerShare(): Money
    {
        return Money::fromDecimal($this->monthly_base_cost)
            ->percentage((string) $this->allocation_percentage);
    }

    public function isEffectiveOn($date): bool
    {
        $from = $this->effective_from;
        $to = $this->effective_to;

        return $from !== null
            && $from->lte($date)
            && ($to === null || $to->gte($date));
    }

    // ── Allocation invariant (handbook §4.2 — must not exceed 100%) ────────

    /** Sum of active allocation % for an asset on a date (optionally excluding a row). */
    public static function totalAllocationFor(string $type, int $assetId, $date, ?int $excludeId = null): float
    {
        return (float) static::query()
            ->active()
            ->effectiveOn($date instanceof \Illuminate\Support\Carbon ? $date->toDateString() : $date)
            ->where('infrastructure_type', $type)
            ->where('infrastructure_id', $assetId)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->sum('allocation_percentage');
    }

    /** Whether adding/updating an allocation would push the asset over 100%. */
    public static function wouldExceed100(string $type, int $assetId, $date, float $newPercentage, ?int $excludeId = null): bool
    {
        return static::totalAllocationFor($type, $assetId, $date, $excludeId) + $newPercentage > 100.0;
    }

    /** Throws if the allocation would over-allocate the asset (call on write). */
    public static function assertValidAllocation(string $type, int $assetId, $date, float $newPercentage, ?int $excludeId = null): void
    {
        if (static::wouldExceed100($type, $assetId, $date, $newPercentage, $excludeId)) {
            $current = static::totalAllocationFor($type, $assetId, $date, $excludeId);
            throw new \App\Centresidence\Exceptions\AllocationExceededException(
                "Allocation for {$type} #{$assetId} would exceed 100% (current {$current}% + {$newPercentage}%)."
            );
        }
    }
}
