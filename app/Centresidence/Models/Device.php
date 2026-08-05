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

    /** Whether this device's cost is attributable to a gateway via topology. */
    public function hasGateway(): bool
    {
        return $this->gateway_id !== null;
    }
}
