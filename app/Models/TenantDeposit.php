<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One security deposit HELD for a tenancy. See the migration for the money invariant: a held
 * deposit is the tenant's money, never the owner's income — never commissioned, never revenue.
 */
class TenantDeposit extends Model
{
    use HasFactory;

    // Lifecycle states.
    public const STATUS_HELD     = 'held';      // collected, sitting as a liability
    public const STATUS_REFUNDED = 'refunded';  // fully returned to the tenant (Phase 4)
    public const STATUS_APPLIED  = 'applied';   // fully set against arrears/damages (Phase 4)
    public const STATUS_SETTLED  = 'settled';   // resolved via a move-out settlement (refund + deductions split; see the settlement)

    protected $fillable = [
        'owner_user_id', 'tenant_id', 'property_id', 'property_unit_id',
        'invoice_id', 'invoice_item_id', 'amount', 'status',
        'released_amount', 'release_method', 'notes', 'held_at', 'released_at',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'released_amount' => 'decimal:2',
        'held_at'         => 'datetime',
        'released_at'     => 'datetime',
    ];

    /** True while the money is still being held (counts toward "deposits held"). */
    public function isHeld(): bool
    {
        return $this->status === self::STATUS_HELD;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'property_unit_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
