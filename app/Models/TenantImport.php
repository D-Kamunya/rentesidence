<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One bulk tenant/unit import run. Owns the uploaded file, the validated preview, and the
 * final processing result.
 */
class TenantImport extends Model
{
    public const STATUS_UPLOADED   = 'uploaded';
    public const STATUS_PREVIEWED  = 'previewed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_COMPLETED_WITH_ERRORS = 'completed_with_errors';
    public const STATUS_FAILED     = 'failed';

    protected $fillable = [
        'owner_user_id', 'original_filename', 'stored_path', 'status', 'options',
        'total_rows', 'valid_rows', 'error_rows',
        'processed_rows', 'created_count', 'updated_count', 'skipped_count',
        'invites_queued', 'invites_sent', 'invites_failed',
        'error_report', 'summary', 'failure_reason',
        'previewed_at', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'options'      => 'array',
        'error_report' => 'array',
        'summary'      => 'array',
        'previewed_at' => 'datetime',
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isDone(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_COMPLETED_WITH_ERRORS, self::STATUS_FAILED], true);
    }
}
