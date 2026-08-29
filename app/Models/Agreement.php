<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One internal e-signature agreement instance: an owner's template, autofilled + frozen
 * for a specific tenant, taken through the sign ceremony and stored as a certified PDF.
 */
class Agreement extends Model
{
    use HasFactory, SoftDeletes;

    // Lifecycle statuses.
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_SENT      = 'sent';
    public const STATUS_VIEWED    = 'viewed';
    public const STATUS_SIGNED    = 'signed';
    public const STATUS_DECLINED  = 'declined';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'owner_user_id', 'tenant_user_id', 'agreement_template_id', 'property_id', 'property_unit_id',
        'title', 'source', 'body', 'template_data', 'original_file_id',
        'status',
        'signer_full_name', 'signature_data', 'signature_method', 'otp_verified_at',
        'sign_otp', 'sign_otp_expires_at',
        'signed_ip', 'signed_user_agent',
        'document_hash', 'certificate_hash', 'verification_code', 'signed_file_id',
        'sent_at', 'viewed_at', 'signed_at', 'declined_at', 'decline_reason',
    ];

    protected $casts = [
        'template_data'       => 'array',
        'otp_verified_at'     => 'datetime',
        'sign_otp_expires_at' => 'datetime',
        'sent_at'         => 'datetime',
        'viewed_at'       => 'datetime',
        'signed_at'       => 'datetime',
        'declined_at'     => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_user_id');
    }

    public function template()
    {
        return $this->belongsTo(AgreementTemplate::class, 'agreement_template_id');
    }

    public function originalFile()
    {
        return $this->belongsTo(FileManager::class, 'original_file_id');
    }

    public function signedFile()
    {
        return $this->belongsTo(FileManager::class, 'signed_file_id');
    }

    public function events()
    {
        return $this->hasMany(AgreementSignatureEvent::class)->orderBy('id');
    }

    public function isSigned(): bool
    {
        return $this->status === self::STATUS_SIGNED;
    }

    /**
     * Append an immutable audit event, capturing the current request context (who / from
     * where). Safe to call outside an HTTP request (actor/ip resolve to null).
     */
    public function logEvent(string $event, array $meta = []): AgreementSignatureEvent
    {
        return $this->events()->create([
            'event'         => $event,
            'actor_user_id' => auth()->id(),
            'actor_role'    => auth()->check() ? (string) auth()->user()->role : null,
            'ip_address'    => request()?->ip(),
            'user_agent'    => request()?->userAgent(),
            'meta'          => $meta ?: null,
            'created_at'    => now(),
        ]);
    }
}
