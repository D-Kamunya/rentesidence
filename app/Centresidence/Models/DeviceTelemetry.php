<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single time-series reading from a device. Metric-agnostic.
 */
class DeviceTelemetry extends Model
{
    use HasFactory;

    protected $table = 'device_telemetry';

    protected $guarded = [];

    protected $casts = [
        'value'       => 'decimal:4',
        'raw'         => 'array',
        'recorded_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
