<?php

namespace App\Centresidence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An external lender in the financing marketplace (handbook §9.2.1).
 */
class FinancePartner extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'api_enabled'                => 'boolean',
        'settlement_account_details' => 'array',
        'configuration_json'         => 'array',
        'onboarded_at'               => 'datetime',
        'origination_fee_percentage' => 'float',
        'servicing_fee_percentage'   => 'float',
    ];

    public const STATUS_ACTIVE     = 'active';
    public const STATUS_INACTIVE   = 'inactive';
    public const STATUS_SUSPENDED  = 'suspended';
    public const STATUS_ONBOARDING = 'onboarding';

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(FinancePartnerDocument::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(FinancePartnerModule::class);
    }

    /**
     * A payout account is set and usable only if it's a B2B-capable, account-tied
     * destination (M-Pesa paybill, bank paybill, or till) — payouts to a phone
     * (B2C) are no longer accepted, since they can't be reconciled to an account.
     */
    public function hasPayoutAccount(): bool
    {
        $a = (array) ($this->settlement_account_details ?? []);

        return match ($a['type'] ?? null) {
            'mpesa_paybill', 'bank' => ! empty($a['paybill']),
            'mpesa_till'            => ! empty($a['till']),
            default                 => false,
        };
    }

    /** Human-readable one-liner for the payout destination, e.g. "Paybill 123456 · Acc REF". */
    public function payoutAccountLabel(): ?string
    {
        $a = (array) ($this->settlement_account_details ?? []);
        return match ($a['type'] ?? null) {
            'mpesa_paybill' => 'M-Pesa Paybill ' . ($a['paybill'] ?? '') . (!empty($a['account']) ? ' · Acc ' . $a['account'] : ''),
            'bank'          => 'Bank ' . ($a['label'] ?? '') . ' · Paybill ' . ($a['paybill'] ?? '') . (!empty($a['account']) ? ' · Acc ' . $a['account'] : ''),
            'mpesa_till'    => 'M-Pesa Till ' . ($a['till'] ?? ''),
            default         => null,
        };
    }
}
