<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A genuine platform failure worth an admin's attention. Written only by
 * {@see \App\Services\SystemIncidentService} through its targeted capture hooks.
 */
class SystemIncident extends Model
{
    public const SEVERITY_WARNING  = 'warning';
    public const SEVERITY_CRITICAL = 'critical';

    public const STATUS_OPEN         = 'open';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';
    public const STATUS_RESOLVED     = 'resolved';

    // Capture types (kept in sync with the hooks that write them).
    public const TYPE_PAYOUT_FAILURE     = 'payout_failure';
    public const TYPE_CALLBACK_EXCEPTION = 'callback_exception';
    public const TYPE_CALLBACK_REJECTED  = 'callback_rejected';
    public const TYPE_JOB_FAILED         = 'job_failed';
    public const TYPE_SCHEDULE_FAILED    = 'schedule_failed';
    public const TYPE_COMMS_FAILURE      = 'comms_failure';

    protected $fillable = [
        'type', 'severity', 'status', 'title', 'message', 'context', 'dedup_key',
        'occurrences', 'first_seen_at', 'last_seen_at', 'alerted_at',
        'acknowledged_by', 'resolved_by', 'resolved_at',
    ];

    protected $casts = [
        'context'       => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at'  => 'datetime',
        'alerted_at'    => 'datetime',
        'resolved_at'   => 'datetime',
    ];

    public function isCritical(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    /** Open = still needs eyes (open OR acknowledged, i.e. not yet resolved). */
    public function scopeUnresolved(Builder $q): Builder
    {
        return $q->whereIn('status', [self::STATUS_OPEN, self::STATUS_ACKNOWLEDGED]);
    }
}
