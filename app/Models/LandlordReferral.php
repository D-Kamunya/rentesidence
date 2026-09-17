<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per invited landlord — the attribution ledger for the invite-a-landlord funnel.
 *
 * Carries the reward state machine. The ledger is always written; whether a reward is
 * cash, non-cash, or nothing is decided by config/referrals.php at confirm time and
 * snapshotted onto the row, so history stays truthful even if the config later changes.
 */
class LandlordReferral extends Model
{
    // Reward state machine.
    public const STATUS_PENDING      = 'pending';       // invite recorded, no lead yet
    public const STATUS_LEAD_CREATED = 'lead_created';  // invited landlord submitted the intake form
    public const STATUS_CONFIRMED    = 'confirmed';     // became a real customer → reward accrued, holding
    public const STATUS_PAID         = 'paid';          // reward paid out after the hold
    public const STATUS_CLAWED_BACK  = 'clawed_back';   // owner churned/refunded in-window → reversed
    public const STATUS_REJECTED     = 'rejected';      // admin / anti-abuse rejected it
    public const STATUS_EXPIRED      = 'expired';       // lead expired without converting

    /** Statuses that count toward graduation + the "already rewarded" guard. */
    public const REWARDED_STATUSES = [self::STATUS_CONFIRMED, self::STATUS_PAID];

    /** Terminal statuses — no further transition. */
    public const CLOSED_STATUSES = [self::STATUS_PAID, self::STATUS_CLAWED_BACK, self::STATUS_REJECTED, self::STATUS_EXPIRED];

    public const REWARD_CASH   = 'cash';
    public const REWARD_CREDIT = 'credit';
    public const REWARD_NONE   = 'none';

    protected $fillable = [
        'referrer_user_id', 'code',
        'invitee_name', 'invitee_phone', 'invitee_email', 'invitee_company',
        'status', 'lead_id', 'owner_id',
        'reward_type', 'reward_amount', 'currency', 'trigger_reason',
        'confirmed_at', 'held_until', 'paid_at', 'clawed_back_at',
        'needs_review', 'meta',
    ];

    protected $casts = [
        'reward_amount'  => 'decimal:2',
        'confirmed_at'   => 'datetime',
        'held_until'     => 'datetime',
        'paid_at'        => 'datetime',
        'clawed_back_at' => 'datetime',
        'needs_review'   => 'boolean',
        'meta'           => 'array',
    ];

    // -------------------------------------------------------------------------
    // Status checks
    // -------------------------------------------------------------------------

    public function isRewarded(): bool
    {
        return in_array($this->status, self::REWARDED_STATUSES, true);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED_STATUSES, true);
    }

    /** A confirmed cash reward whose holding period has elapsed is ready to pay. */
    public function isPayable(): bool
    {
        return $this->status === self::STATUS_CONFIRMED
            && $this->reward_type === self::REWARD_CASH
            && (float) $this->reward_amount > 0
            && ! $this->needs_review
            && $this->held_until
            && $this->held_until->isPast();
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }
}
