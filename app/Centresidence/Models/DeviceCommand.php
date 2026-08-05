<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A downlink command dispatched to a device (credit tokens, actuate, …).
 */
class DeviceCommand extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'payload'   => 'array',
        'response'  => 'array',
        'issued_at' => 'datetime',
        'acked_at'  => 'datetime',
    ];

    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENT   = 'sent';
    public const STATUS_ACKED  = 'acked';
    public const STATUS_FAILED = 'failed';

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
