<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Append-only audit event for an agreement's signing ceremony. No updated_at — a row is
 * written once and never mutated (it's evidence).
 */
class AgreementSignatureEvent extends Model
{
    public const UPDATED_AT = null;

    public const EVENT_SENT         = 'sent';
    public const EVENT_VIEWED       = 'viewed';
    public const EVENT_OTP_SENT     = 'otp_sent';
    public const EVENT_OTP_VERIFIED = 'otp_verified';
    public const EVENT_CONSENTED    = 'consented';
    public const EVENT_SIGNED       = 'signed';
    public const EVENT_DECLINED     = 'declined';
    public const EVENT_DOWNLOADED   = 'downloaded';

    protected $fillable = [
        'agreement_id', 'event', 'actor_user_id', 'actor_role', 'ip_address', 'user_agent', 'meta', 'created_at',
    ];

    protected $casts = [
        'meta'       => 'array',
        'created_at' => 'datetime',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }
}
