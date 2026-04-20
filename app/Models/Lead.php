<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    public const OWNERSHIP_DURATION_DAYS = 60;

    /**
     * Leads in these statuses are permanently closed.
     * They never expire and never return to the marketplace.
     */
    public const CLOSED_STATUSES = [
        'converted',
        'rejected',
        'lost',
    ];

    /**
     * Leads in these statuses have meaningful active work happening.
     * The ownership clock is paused — expiry must not fire.
     *
     * demo_scheduled   : a meeting is booked, expiring now would be disruptive
     * demo_completed   : affiliate is working up a proposal / next step
     * pending_conversion: conversion is being processed, can't yank it away
     * trial            : prospect is actively evaluating the product
     */
    public const PROTECTED_STATUSES = [
        'demo_scheduled',
        'demo_completed',
        'pending_conversion',
        'trial',
    ];

    protected $fillable = [
        'company_id',
        'affiliate_id',
        'owner_id', 
        'contact_person_name',
        'contact_person_role',
        'temperature',
        'status',
        'source',
        'marketplace_cycles',
        'marketplace_status',
        'claimed_at',
        'marketplace_at',
        'notes',
        'last_activity_at',
        'demo_scheduled_at',
        'converted_at',
    ];

    protected $casts = [
        'claimed_at'           => 'datetime',
        'marketplace_at'       => 'datetime',
        'ownership_expires_at' => 'datetime',
        'last_activity_at'     => 'datetime',
        'demo_scheduled_at'    => 'datetime',
        'converted_at'         => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Lead $lead) {
            if (! $lead->ownership_expires_at) {
                $lead->ownership_expires_at = now()->addDays(self::OWNERSHIP_DURATION_DAYS);
            }
        });

        // No expiry logic here. The cron job owns that responsibility.
    }

    // -------------------------------------------------------------------------
    // Status checks
    // -------------------------------------------------------------------------

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED_STATUSES);
    }

    public function isProtected(): bool
    {
        return in_array($this->status, self::PROTECTED_STATUSES);
    }

    /**
     * The single source of truth for whether a lead is eligible to expire.
     *
     * Rules:
     *  - Must not already be expired or closed
     *  - Must not be in a protected status (active work in progress)
     *  - ownership_expires_at must exist and be in the past
     */
    public function shouldExpire(): bool
    {
        if ($this->status === 'expired') {
            return false;
        }

        if ($this->isClosed()) {
            return false;
        }

        if ($this->isProtected()) {
            return false;
        }

        if (! $this->ownership_expires_at || ! $this->ownership_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Marketplace logic
    // -------------------------------------------------------------------------

    public function isMarketplaceLead(): bool
    {
        return $this->source === 'admin' || $this->marketplace_status !== null;
    }

    /**
     * Only admin-sourced leads cycle back to the marketplace after expiry.
     * Affiliate-submitted leads are permanently that affiliate's lead.
     */
    public function shouldReturnToMarketplace(): bool
    {
        return $this->status === 'expired'
            && $this->source === 'admin'
            && ! $this->isClosed();
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function expire(): bool
    {
        if (! $this->shouldExpire()) {
            return false;
        }

        $this->status = 'expired';
        $this->last_activity_at = now();

        return $this->save();
    }

    public function renew(): bool
    {
        if ($this->isClosed()) {
            return false;
        }

        if ($this->ownership_expires_at > now()) {
            return false;
        }

        $this->ownership_expires_at = now()->addDays(self::OWNERSHIP_DURATION_DAYS);
        $this->status = 'active';
        $this->last_activity_at = now();

        return $this->save();
    }

    // -------------------------------------------------------------------------
    // Activity helpers
    // -------------------------------------------------------------------------

    public function latestTrialActivity(): ?LeadActivity
    {
        return $this->activities
            ->whereIn('type', ['trial_requested', 'trial_extension', 'trial_expired', 'conversion_rejected'])
            ->sortByDesc('created_at')
            ->first();
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function suggestions()
    {
        return $this->hasMany(LeadSuggestion::class);
    }

    public function owner() 
    { 
        return $this->belongsTo(Owner::class); 
    }
}