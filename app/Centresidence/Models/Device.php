<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A generic infrastructure endpoint. Its utility identity comes from its
 * property_module; it is attributed to infrastructure cost via its gateway.
 */
class Device extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_simulated'   => 'boolean',
        'activated_at'   => 'datetime',
        'deactivated_at' => 'datetime',
        'last_seen_at'   => 'datetime',
        'metadata'       => 'array',
    ];

    public const STATUS_PROVISIONING  = 'provisioning';
    public const STATUS_ACTIVE        = 'active';
    public const STATUS_INACTIVE      = 'inactive';
    public const STATUS_DECOMMISSIONED = 'decommissioned';

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function propertyModule(): BelongsTo
    {
        return $this->belongsTo(PropertyModule::class);
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class);
    }

    /** The specific unit this meter serves (legacy PropertyUnit). Drives wallet attribution. */
    public function propertyUnit(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PropertyUnit::class, 'property_unit_id');
    }

    public function telemetry(): HasMany
    {
        return $this->hasMany(DeviceTelemetry::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /** The real unit this meter serves (e.g. "A1"), or null if unassigned. */
    public function unitName(): ?string
    {
        return optional($this->propertyUnit)->unit_name;
    }

    /**
     * The meter's name for display, with any stale sequential provisioning
     * suffix ("… Unit 3") stripped once we can show the REAL unit alongside —
     * so it reads "Smart Water Meter" + a "Unit A1" chip, not a redundant
     * "Smart Water Meter Unit 3 · Unit A1".
     */
    public function cleanName(): string
    {
        $name = $this->name ?: __('Device');

        if ($this->unitName()) {
            $name = preg_replace('/\s*Unit\s+\d+\s*$/i', '', $name);
        }

        return trim($name) ?: __('Device');
    }

    /**
     * A definitive, human label for the meter — its type plus the unit it
     * serves, so an owner can identify a specific meter from the device itself
     * ("Smart Water Meter · A1"). Falls back to the stored name when the device
     * is not yet linked to a unit.
     */
    public function displayLabel(): string
    {
        $base = $this->name ?: __('Device');
        $unit = $this->unitName();

        return $unit ? ($base . ' · ' . $unit) : $base;
    }

    /** Whether this device's cost is attributable to a gateway via topology. */
    public function hasGateway(): bool
    {
        return $this->gateway_id !== null;
    }
}
