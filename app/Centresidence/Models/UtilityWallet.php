<?php

namespace App\Centresidence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A tenant's prepaid token balance for a metered module (handbook §7).
 */
class UtilityWallet extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'balance_units'         => 'decimal:4',
        'total_purchased_units' => 'decimal:4',
        'total_consumed_units'  => 'decimal:4',
    ];

    public function propertyModule(): BelongsTo
    {
        return $this->belongsTo(PropertyModule::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_user_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(TokenPurchase::class);
    }

    public function consumption(): HasMany
    {
        return $this->hasMany(UtilityConsumption::class);
    }

    /** Credit units (tenant always receives full units — continuity). */
    public function creditUnits(string $units): void
    {
        $this->balance_units = bcadd((string) $this->balance_units, $units, 4);
        $this->total_purchased_units = bcadd((string) $this->total_purchased_units, $units, 4);
        $this->save();
    }

    public function debitUnits(string $units): void
    {
        $this->balance_units = bcsub((string) $this->balance_units, $units, 4);
        $this->total_consumed_units = bcadd((string) $this->total_consumed_units, $units, 4);
        $this->save();
    }
}
