<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Token economics for a metered property_module (handbook §7.2).
 *
 * Backed by `module_token_config`. The owner-revenue figure is kept consistent
 * with the corrected formula (see computeOwnerRevenuePerUnit) on every save so
 * stored values never drift from units_per_kes + commission.
 */
class ModuleTokenConfig extends Model
{
    use HasFactory;

    protected $table = 'module_token_config';

    protected $guarded = [];

    protected $casts = [
        'units_per_kes'                            => 'decimal:4',
        'centresidence_commission_per_token_unit'  => 'decimal:4',
        'owner_revenue_per_token_unit'             => 'decimal:4',
        'is_active'                                 => 'boolean',
    ];

    protected static function booted(): void
    {
        // Keep owner_revenue_per_token_unit derived, never hand-entered wrong.
        static::saving(function (ModuleTokenConfig $config) {
            if ($config->units_per_kes !== null) {
                $config->owner_revenue_per_token_unit = self::computeOwnerRevenuePerUnit(
                    (string) $config->units_per_kes,
                    (string) ($config->centresidence_commission_per_token_unit ?? '0')
                );
            }
        });
    }

    public function propertyModule(): BelongsTo
    {
        return $this->belongsTo(PropertyModule::class);
    }

    /**
     * Corrected handbook §7.2 formula (the printed one is dimensionally wrong;
     * the §7.3 worked example is the source of truth):
     *
     *   price_per_unit         = 1 / units_per_kes              (KES per unit)
     *   owner_revenue_per_unit = price_per_unit − commission_per_unit
     *
     * Worked example: units_per_kes = 5, commission = 0.02/litre
     *   price_per_unit = 0.20, owner_revenue = 0.18 → 5 × 0.18 = KES 0.90/KES1. ✓
     *
     * Returned as a 4-dp decimal string (bcmath, no float drift).
     */
    public static function computeOwnerRevenuePerUnit(string $unitsPerKes, string $commissionPerUnit): string
    {
        if (bccomp($unitsPerKes, '0', 6) <= 0) {
            return '0.0000';
        }

        $pricePerUnit = bcdiv('1', $unitsPerKes, 8);
        $ownerRevenue = bcsub($pricePerUnit, $commissionPerUnit, 8);

        // Normalise to 4 dp for storage.
        return bcadd($ownerRevenue, '0', 4);
    }

    /** Price a tenant pays per token unit (KES per unit). */
    public function pricePerUnit(): string
    {
        if (bccomp((string) $this->units_per_kes, '0', 6) <= 0) {
            return '0.0000';
        }

        return bcadd(bcdiv('1', (string) $this->units_per_kes, 8), '0', 4);
    }
}
