<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A support conversation between any account holder (owner/affiliate/finance-partner/tenant/…)
 * and admin. Reusable rail — see the migration.
 */
class SupportTicket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'requester_user_id', 'requester_role', 'subject', 'category',
        'status', 'priority', 'admin_unread', 'requester_unread', 'last_reply_at',
    ];

    protected $casts = [
        'admin_unread'     => 'boolean',
        'requester_unread' => 'boolean',
        'last_reply_at'    => 'datetime',
    ];

    public const STATUS_OPEN     = 'open';      // awaiting admin
    public const STATUS_ANSWERED = 'answered';  // admin replied, awaiting requester
    public const STATUS_RESOLVED = 'resolved';  // admin marked done (still viewable/reopenable)
    public const STATUS_CLOSED   = 'closed';    // shut — no further replies

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'support_ticket_id')->oldest();
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }
}
