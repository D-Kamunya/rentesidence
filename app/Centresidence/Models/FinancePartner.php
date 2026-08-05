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
}
