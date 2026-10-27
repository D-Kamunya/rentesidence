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
        'product',
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
        'demo_meeting_link',
        'converted_at',
        'free_invite_token',
        'free_invite_email',
        'free_invite_sent_at',
        'free_invite_expires_at',
        'free_invite_accepted_at',
        'free_invite_count',
    ];

    protected $casts = [
        'claimed_at'              => 'datetime',
        'marketplace_at'          => 'datetime',
        'ownership_expires_at'    => 'datetime',
        'last_activity_at'        => 'datetime',
        'demo_scheduled_at'       => 'datetime',
        'converted_at'            => 'datetime',
        'free_invite_sent_at'     => 'datetime',
        'free_invite_expires_at'  => 'datetime',
        'free_invite_accepted_at' => 'datetime',
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
        return in_array($this->status, self::PROTECTED_STATUSES) || $this->hasPendingFreeInvite();
    }

    /**
     * A lead with a LIVE "Set up on Free" invite out (sent, not yet accepted, not yet lapsed) is
     * active work — the affiliate is waiting on the owner to activate. Treat it like a protected
     * status: the ownership clock pauses, so it never shows "Expired" / offers Renew while the
     * owner still has a valid invite to accept. Once the invite is accepted (lead converts) or
     * lapses, this returns false and the normal clock resumes.
     */
    public function hasPendingFreeInvite(): bool
    {
        return $this->free_invite_sent_at !== null
            && $this->free_invite_accepted_at === null
            && $this->free_invite_expires_at !== null
            && $this->free_invite_expires_at->isFuture();
    }

    // -------------------------------------------------------------------------
    // Display lifecycle — the SINGLE SOURCE OF TRUTH for how a lead presents
    // -------------------------------------------------------------------------

    /**
     * The one canonical lifecycle state a lead presents as, so every surface (the leads-list
     * badges + action button, and the detail page's hero badges + expiry + actions) derives from
     * the SAME definition and can never contradict each other. Precedence runs most-definitive
     * first: a terminal status, then the live Free-invite overlay, then protected pipeline work,
     * then the ownership clock.
     *
     *   won                  → converted (a win — never "closed"/"renew")
     *   rejected / lost      → closed by the affiliate (terminal)
     *   expired              → the cron flipped status to 'expired' (sales locked, renewable)
     *   trial                → on an active trial
     *   awaiting_activation  → a live "Set up on Free" invite is out, owner hasn't activated yet
     *   working              → in a protected pipeline stage (demo scheduled/completed, pending conversion)
     *   lapsed               → still open but the 60-day ownership window has passed (renewable, still workable)
     *   active               → open, being worked, clock running
     */
    public function displayState(): string
    {
        return match (true) {
            $this->status === 'converted' => 'won',
            $this->status === 'rejected'  => 'rejected',
            $this->status === 'lost'      => 'lost',
            $this->status === 'expired'   => 'expired',
            $this->status === 'trial'     => 'trial',
            $this->hasPendingFreeInvite() => 'awaiting_activation',
            in_array($this->status, ['demo_scheduled', 'demo_completed', 'pending_conversion'], true) => 'working',
            ($this->ownership_expires_at && $this->ownership_expires_at->isPast()) => 'lapsed',
            default => 'active',
        };
    }

    /** A converted win. */
    public function isWon(): bool
    {
        return $this->displayState() === 'won';
    }

    /**
     * Sales actions are locked (terminal OR cron-expired). Note this is DISTINCT from isClosed():
     * a cron-flipped 'expired' lead locks sales but is not "closed" (it can still be renewed), and
     * a 'lapsed' lead (date passed, status still open) is NOT locked — it's still workable.
     */
    public function isLockedForSales(): bool
    {
        return in_array($this->displayState(), ['won', 'rejected', 'lost', 'expired'], true);
    }

    /** The ownership window has run out and the lead can be renewed (cron-expired or date-lapsed). */
    public function canRenew(): bool
    {
        return in_array($this->displayState(), ['expired', 'lapsed'], true);
    }

    /** The lead is open/being worked, so Edit (not Renew, not a closed badge) is the right action. */
    public function canEdit(): bool
    {
        return in_array($this->displayState(), ['active', 'working', 'trial', 'awaiting_activation'], true);
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