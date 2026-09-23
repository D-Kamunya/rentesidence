<?php

namespace App\Centresidence\Models;

use App\Centresidence\Support\Money;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A token purchase with embedded-commission accounting (handbook §7.3).
 */
class TokenPurchase extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount'                       => 'decimal:2',
        'units'                        => 'decimal:4',
        'units_per_kes_snapshot'       => 'decimal:4',
        'commission_per_unit_snapshot' => 'decimal:4',
        'centresidence_commission'     => 'decimal:2',
        'owner_revenue_gross'          => 'decimal:2',
        'fallback_deducted'            => 'decimal:2',
        'owner_revenue_net'            => 'decimal:2',
        'purchased_at'                 => 'datetime',
    ];

    public const STATUS_PENDING   = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED    = 'failed';

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(UtilityWallet::class, 'utility_wallet_id');
    }

    public function propertyModule(): BelongsTo
    {
        return $this->belongsTo(PropertyModule::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_user_id');
    }

    public function deviceCommand(): BelongsTo
    {
        return $this->belongsTo(DeviceCommand::class);
    }

    public function commissionMoney(): Money
    {
        return Money::fromDecimal($this->centresidence_commission);
    }
}
