<?php

namespace App\Centresidence\Models;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An owner's request for a site survey + bespoke quotation on a custom infrastructure install
 * (e.g. reticulated gas) that can't be priced from the standard per-unit catalogue. Lifecycle:
 * requested → surveyed → quoted → applied (owner takes the quote into the financing flow) /
 * cancelled. The financing rail itself is utility-agnostic (FinanceApplication.requested_amount),
 * so this only adds the survey→quote orchestration in front of it.
 */
class FieldStudyRequest extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'quoted_amount' => 'decimal:2',
        'quoted_at'     => 'datetime',
    ];

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_SURVEYED  = 'surveyed';
    public const STATUS_QUOTED    = 'quoted';
    public const STATUS_APPLIED   = 'applied';
    public const STATUS_CANCELLED = 'cancelled';

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** A quote the owner can act on. */
    public function isQuoted(): bool
    {
        return $this->status === self::STATUS_QUOTED && $this->quoted_amount !== null;
    }
}
