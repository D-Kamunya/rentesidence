<?php

namespace App\Centresidence\Models;

use App\Centresidence\Support\Money;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The monthly bill for a SUBSCRIPTION-mode owner's deployed modules
 * (handbook §8.2). Backed by `centresidence_commission_invoices`.
 *
 * ⚠️ NAMING (post-pivot 2026-06): despite the legacy "commission" names, the
 * `metered_commission_total` / `non_metered_commission_total` columns now hold
 * the modules' INFRASTRUCTURE COST (software fee + LoRaWAN gateway usage),
 * NOT commission. Per-token commission is a SEPARATE, gas-only income share
 * carved by the TokenEngine and never appears here. Read these as
 * "metered_infra_total" / "non_metered_infra_total". `subscription_amount` is
 * the owner's plan price; `total_amount` = plan + module infra costs. The
 * `metered_*` portion is what the token/rent fallback can recover when overdue.
 * (A clean model+table+column rename is a queued, dedicated refactor.)
 */
class CentresidenceCommissionInvoice extends Model
{
    use HasFactory;

    protected $table = 'centresidence_commission_invoices';

    protected $guarded = [];

    protected $casts = [
        'billing_month'                    => 'date',
        'subscription_amount'              => 'decimal:2',
        'metered_commission_breakdown'     => 'array',
        'metered_commission_total'         => 'decimal:2',
        'metered_paid_total'               => 'decimal:2',
        'non_metered_commission_breakdown' => 'array',
        'non_metered_commission_total'     => 'decimal:2',
        'infrastructure_cost_breakdown'    => 'array',
        'total_amount'                     => 'decimal:2',
        'fallback_deduction_active'        => 'boolean',
        'fallback_deduction_started_at'    => 'datetime',
        'fallback_metered_cleared_at'      => 'datetime',
        'paid_at'                          => 'datetime',
    ];

    public const STATUS_PENDING        = 'pending';
    public const STATUS_PAID           = 'paid';
    public const STATUS_OVERDUE        = 'overdue';
    public const STATUS_PARTIALLY_PAID = 'partially_paid';
    public const STATUS_WAIVED         = 'waived';

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_OVERDUE,
            self::STATUS_PARTIALLY_PAID,
        ]);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function totalMoney(): Money
    {
        return Money::fromDecimal($this->total_amount);
    }

    /** Metered amount eligible for the token-deduction fallback (WP4). */
    public function meteredMoney(): Money
    {
        return Money::fromDecimal($this->metered_commission_total);
    }

    /** Outstanding metered (fallback-eligible) amount still to recover. */
    public function meteredOutstanding(): Money
    {
        return Money::fromDecimal($this->metered_commission_total)
            ->minus(Money::fromDecimal($this->metered_paid_total ?? '0'));
    }

    public function hasNonMetered(): bool
    {
        return (float) $this->non_metered_commission_total > 0;
    }
}
