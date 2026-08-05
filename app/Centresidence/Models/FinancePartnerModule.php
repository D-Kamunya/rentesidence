<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A partner's financing product for a module (handbook §9.2.2). Defines rates,
 * fees, tenor, settlement and underwriting requirements.
 */
class FinancePartnerModule extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'interest_rate'                  => 'decimal:2',
        'penalty_rate'                   => 'decimal:2',
        'processing_fee_percentage'      => 'decimal:2',
        'processing_fee_flat'            => 'decimal:2',
        'min_amount'                     => 'decimal:2',
        'max_amount'                     => 'decimal:2',
        'min_occupancy_rate'             => 'decimal:2',
        'max_rent_deduction_percentage'  => 'decimal:2',
        'max_total_obligation_ratio'     => 'decimal:2',
        'early_repayment_penalty_percentage' => 'decimal:2',
        'requires_existing_obligation_check' => 'boolean',
        'requires_owner_kyc'             => 'boolean',
        'requires_property_valuation'    => 'boolean',
        'daily_settlement_enabled'       => 'boolean',
        'monthly_settlement_enabled'     => 'boolean',
        'early_repayment_allowed'        => 'boolean',
        'insurance_required'             => 'boolean',
        'configuration_json'             => 'array',
    ];

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_INACTIVE  = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(FinancePartner::class, 'finance_partner_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function underwritingRules(): HasMany
    {
        return $this->hasMany(UnderwritingRule::class);
    }

    public function documentRequirements(): HasMany
    {
        return $this->hasMany(ApplicationDocumentRequirement::class);
    }
}
