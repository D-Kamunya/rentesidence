<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A LoRaWAN gateway — shared Centresidence infrastructure. Its cost split lives
 * in infrastructure_topology, not here.
 */
class Gateway extends Model
{
    use HasFactory, SoftDeletes;

    // Prefixed to avoid colliding with the legacy payment `gateways` table.
    protected $table = 'cs_gateways';

    protected $guarded = [];

    protected $casts = [
        'latitude'     => 'decimal:7',
        'longitude'    => 'decimal:7',
        'is_simulated' => 'boolean',
        'last_seen_at' => 'datetime',
        'metadata'     => 'array',
    ];

    public const STATUS_ACTIVE      = 'active';
    public const STATUS_INACTIVE    = 'inactive';
    public const STATUS_MAINTENANCE = 'maintenance';

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function activeDevices(): HasMany
    {
        return $this->devices()->where('status', Device::STATUS_ACTIVE);
    }

    /** Topology rows that allocate this gateway's cost across owners/properties. */
    public function topologyAllocations(): HasMany
    {
        return $this->hasMany(InfrastructureTopology::class, 'infrastructure_id')
            ->where('infrastructure_type', InfrastructureTopology::TYPE_GATEWAY);
    }
}
