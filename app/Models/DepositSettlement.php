<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The itemized record of a move-out deposit settlement (Model A). See the migration: held −
 * deductions = refund. The tenant confirm/dispute status is added in the attestation slice.
 */
class DepositSettlement extends Model
{
    use HasFactory;

    public const STATUS_RECORDED  = 'recorded';   // owner logged it
    public const STATUS_CONFIRMED = 'confirmed';  // tenant confirmed receipt (attestation slice)
    public const STATUS_DISPUTED  = 'disputed';   // tenant disputes it (attestation slice)

    protected $fillable = [
        'tenant_id', 'owner_user_id', 'property_id', 'property_unit_id', 'vacation_notice_id',
        'deposit_held', 'total_deductions', 'refund_amount',
        'refund_method', 'refund_reference', 'refund_date',
        'status', 'notes', 'settled_at',
        'tenant_response_note', 'tenant_responded_at',
        'owner_response_note', 'owner_responded_at',
    ];

    protected $casts = [
        'deposit_held'        => 'decimal:2',
        'total_deductions'    => 'decimal:2',
        'refund_amount'       => 'decimal:2',
        'refund_date'         => 'date',
        'settled_at'          => 'datetime',
        'tenant_responded_at' => 'datetime',
        'owner_responded_at'  => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(DepositSettlementItem::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
