<?php

namespace App\Centresidence\Models;

use App\Centresidence\Support\Money;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An active financing facility (handbook §9.4.2).
 */
class FinanceFacility extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'disbursed_amount'      => 'decimal:2',
        'principal_amount'      => 'decimal:2',
        'platform_fee_amount'   => 'decimal:2',
        'platform_fee_settled'  => 'boolean',
        'platform_fee_settled_at' => 'datetime',
        'interest_rate'         => 'decimal:2',
        'penalty_rate'          => 'decimal:2',
        'processing_fee_charged' => 'decimal:2',
        'total_repayable'       => 'decimal:2',
        'outstanding_principal' => 'decimal:2',
        'outstanding_interest'  => 'decimal:2',
        'outstanding_penalty'   => 'decimal:2',
        'monthly_target'        => 'decimal:2',
        'deduction_percentage'  => 'decimal:2',
        'accelerated_repayment' => 'boolean',
        'disbursement_date'     => 'date',
        'first_repayment_date'  => 'date',
        'maturity_date'         => 'date',
        'defaulted_at'          => 'datetime',
        'completed_at'          => 'datetime',
        'suspended_at'          => 'datetime',
        'resumed_at'            => 'datetime',
        'disbursed_at'          => 'datetime',
        'early_settlement_at'   => 'datetime',
        'origination_fee_amount'    => 'decimal:2',
        'origination_fee_collected' => 'decimal:2',
    ];

    /** Origination fee still owed by the partner (only collected once disbursed). */
    public function originationOutstanding(): float
    {
        return max(0.0, (float) $this->origination_fee_amount - (float) $this->origination_fee_collected);
    }

    // Disbursement lifecycle — a facility is only REPAYABLE once disbursed.
    public const DISBURSE_AWAITING = 'awaiting';           // approved, funds not yet released
    public const DISBURSE_PENDING  = 'pending_confirmation'; // recorded, awaiting the payee's confirmation
    public const DISBURSE_DONE     = 'disbursed';           // money confirmed released

    // Early-settlement lifecycle — completed only once the payoff is confirmed paid.
    public const EARLY_PENDING = 'pending';   // payoff initiated, awaiting confirmation
    public const EARLY_SETTLED = 'settled';   // payoff confirmed → facility completed

    public const STATUS_ACTIVE       = 'active';
    public const STATUS_COMPLETED    = 'completed';
    public const STATUS_SUSPENDED    = 'suspended';
    public const STATUS_DEFAULTED    = 'defaulted';
    public const STATUS_RESTRUCTURED = 'restructured';
    public const STATUS_RECOVERED    = 'recovered';
    public const STATUS_WRITTEN_OFF  = 'written_off';

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /** Only facilities whose money has actually been released — the ones rent may repay. */
    public function scopeDisbursed($query)
    {
        return $query->where('disbursement_status', self::DISBURSE_DONE);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(FinanceApplication::class, 'finance_application_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(FinancePartner::class, 'finance_partner_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(RepaymentSchedule::class)->orderBy('period_number');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FacilityTransaction::class);
    }

    public function outstandingTotal(): Money
    {
        return Money::fromDecimal($this->outstanding_principal)
            ->plus(Money::fromDecimal($this->outstanding_interest))
            ->plus(Money::fromDecimal($this->outstanding_penalty));
    }

    public function isDisbursed(): bool
    {
        // Authoritative gate = the disbursement lifecycle status (a facility can be
        // created active before funds are released; disbursement_date alone is stale).
        return $this->disbursement_status === self::DISBURSE_DONE;
    }
}
