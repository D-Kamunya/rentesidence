<?php

namespace App\Centresidence\Models;

use App\Centresidence\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A standardized, financeable line item for a module (handbook §10). A finance
 * application reads `unit_price` from here to compute base_cost.
 *
 * Backed by the `module_pricing_catalogue` table.
 */
class ModulePricingCatalogueItem extends Model
{
    use HasFactory;

    protected $table = 'module_pricing_catalogue';

    protected $guarded = [];

    protected $casts = [
        'unit_price'        => 'decimal:2',
        'installation_cost' => 'decimal:2',
        'is_active'         => 'boolean',
        'effective_from'    => 'date',
        'effective_to'      => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function unitPriceMoney(): Money
    {
        return Money::fromDecimal($this->unit_price, $this->currency ?? 'KES');
    }

    /** base_cost = unit_price × quantity (handbook §9.3 step 3). */
    public function baseCost(int $quantity): Money
    {
        return $this->unitPriceMoney()->timesQuantity($quantity);
    }

    public function installationCost(int $quantity): Money
    {
        return Money::fromDecimal($this->installation_cost ?? '0', $this->currency ?? 'KES')
            ->timesQuantity($quantity);
    }

    /** Full self-financed cost = (hardware + installation) × quantity. */
    public function fullCost(int $quantity): Money
    {
        return $this->baseCost($quantity)->plus($this->installationCost($quantity));
    }
}
