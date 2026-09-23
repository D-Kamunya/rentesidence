<?php

namespace App\Centresidence\Models;

use App\Centresidence\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One composable element of what Centresidence earns on a module
 * (handbook §5). Components sum to the module's total cost.
 *
 * @property string $cost_model
 * @property string $rate
 * @property bool   $requires_gateway
 * @property bool   $is_fallback_eligible
 */
class ModuleCostComponent extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'rate'                 => 'decimal:4',
        'is_prorated'          => 'boolean',
        'requires_gateway'     => 'boolean',
        'is_fallback_eligible' => 'boolean',
    ];

    public const COST_MODEL_PER_ACTIVE_DEVICE     = 'per_active_device';
    public const COST_MODEL_PER_GATEWAY_ALLOCATION = 'per_gateway_allocation';
    public const COST_MODEL_PER_UNIT_CONSUMED     = 'per_unit_consumed';
    public const COST_MODEL_FLAT_MONTHLY          = 'flat_monthly';

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /** This component's rate as a Money (per the configured currency). */
    public function rateMoney(): Money
    {
        return Money::fromDecimal($this->rate, $this->currency ?? 'KES');
    }

    /**
     * Cost contribution for a per_active_device component:
     *   rate × active_device_count, prorated by active-day fraction if set.
     */
    public function perDeviceCost(int $activeDeviceCount, int $activeDays = 30, int $periodDays = 30): Money
    {
        $cost = $this->rateMoney()->timesQuantity($activeDeviceCount);

        if ($this->is_prorated) {
            $cost = $cost->prorate($activeDays, $periodDays);
        }

        return $cost;
    }
}
