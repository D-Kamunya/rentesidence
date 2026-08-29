<?php

namespace App\Centresidence\Models;

use App\Centresidence\Support\Money;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * An owner's financing application (handbook §9.3.2). Lifecycle status moves
 * through the state machine in FinanceApplicationService; every change is
 * logged to application_status_history.
 */
class FinanceApplication extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'base_cost'                   => 'decimal:2',
        'platform_fee_percentage'     => 'decimal:2',
        'platform_fee_amount'         => 'decimal:2',
        'requested_amount'            => 'decimal:2',
        'approved_amount'             => 'decimal:2',
        'interest_rate_snapshot'      => 'decimal:2',
        'repayment_percentage'        => 'decimal:2',
        'estimated_monthly_repayment' => 'decimal:2',
        'underwriting_result_json'    => 'array',
        'application_data_json'       => 'array',
        'owner_consent'               => 'boolean',
        'owner_consent_at'            => 'datetime',
        'submitted_at'                => 'datetime',
        'under_review_at'             => 'datetime',
        'approved_at'                 => 'datetime',
        'rejected_at'                 => 'datetime',
        'disbursed_at'                => 'datetime',
    ];

    public const STATUS_DRAFT        = 'draft';
    public const STATUS_SUBMITTED    = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED     = 'approved';
    public const STATUS_REJECTED     = 'rejected';
    public const STATUS_DISBURSED    = 'disbursed';
    public const STATUS_WITHDRAWN    = 'withdrawn';
    public const STATUS_CANCELLED    = 'cancelled';

    // ── Relationships ─────────────────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(FinancePartner::class, 'finance_partner_id');
    }

    public function partnerModule(): BelongsTo
    {
        return $this->belongsTo(FinancePartnerModule::class, 'finance_partner_module_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class)->orderBy('id');
    }

    public function facility(): HasOne
    {
        return $this->hasOne(FinanceFacility::class, 'finance_application_id')->latestOfMany();
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function requestedAmountMoney(): Money
    {
        return Money::fromDecimal($this->requested_amount);
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }
}
