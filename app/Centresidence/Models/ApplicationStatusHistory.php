<?php

namespace App\Centresidence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable status-change log for an application (handbook §9.3.3). Append-only:
 * only created_at is tracked.
 */
class ApplicationStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'application_status_history';

    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'metadata_json' => 'array',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(FinanceApplication::class, 'finance_application_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
