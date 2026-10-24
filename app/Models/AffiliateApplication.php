<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AffiliateApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'location',
        'coverage_area', 'pitch', 'experience', 'finance_contacts',
        'source', 'status', 'converted_user_id', 'admin_note',
        'agreed_at', 'consent_ip', 'consent_version',
    ];

    protected $casts = [
        'agreed_at' => 'datetime',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Bump this whenever the commission-only Partner Terms shown on the apply page change in a way
     * an applicant should re-consent to. Stored on the row at submit, so a dispute can be answered
     * with the exact terms version the applicant accepted.
     */
    public const CONSENT_VERSION = 'partner-terms-2026-10';

    /** The single canonical sentence an applicant accepts — mirrors the apply-page terms. */
    public static function consentStatement(): string
    {
        return __('I have read and accept the Centresidence Partner Terms. I understand this is a commission-only role — there is no salary or retainer — and that I earn recurring commission on what the owners I introduce actually do on the platform.');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
