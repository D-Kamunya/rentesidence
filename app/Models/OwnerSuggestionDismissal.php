<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single owner's dismissal/snooze of one Upgrade-Advisor suggestion. The advisor computes
 * suggestions live; this is the only persisted state, so a hidden nudge stays hidden across
 * devices until the snooze expires.
 */
class OwnerSuggestionDismissal extends Model
{
    protected $fillable = ['owner_user_id', 'suggestion_key', 'snoozed_until'];

    protected $casts = ['snoozed_until' => 'datetime'];

    /** Still hiding the suggestion? (indefinite dismissal, or snooze not yet elapsed) */
    public function isActive(): bool
    {
        return $this->snoozed_until === null || $this->snoozed_until->isFuture();
    }
}
