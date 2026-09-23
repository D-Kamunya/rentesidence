<?php

namespace App\Centresidence\Models;

use App\Centresidence\Support\Money;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Separate infrastructure invoice for non-metered costs (handbook §8.3).
 * Backed by `owner_infrastructure_invoices`.
 */
class OwnerInfrastructureInvoice extends Model
{
    use HasFactory;

    protected $table = 'owner_infrastructure_invoices';

    protected $guarded = [];

    protected $casts = [
        'billing_month'  => 'date',
        'breakdown_json' => 'array',
        'total_amount'   => 'decimal:2',
        'paid_total'     => 'decimal:2',
        'paid_at'        => 'datetime',
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

    /** Amount still owed = total − recovered so far. */
    public function outstanding(): Money
    {
        return Money::fromDecimal($this->total_amount)
            ->minus(Money::fromDecimal($this->paid_total ?? '0'));
    }
}
