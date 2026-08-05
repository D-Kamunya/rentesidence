<?php

namespace Tests\Unit\Centresidence;

use App\Centresidence\Models\ModuleTokenConfig;
use Tests\TestCase;

/**
 * Proves the corrected handbook §7.2 token-economics formula in isolation
 * (pure, no DB). The handbook's printed formula is dimensionally wrong; the
 * §7.3 worked example is the source of truth.
 */
class TokenConfigFormulaTest extends TestCase
{
    /** units_per_kes=5, commission=0.02/unit → owner nets 0.18/unit. */
    public function test_owner_revenue_per_unit_matches_worked_example(): void
    {
        $owner = ModuleTokenConfig::computeOwnerRevenuePerUnit('5', '0.02');
        $this->assertSame('0.1800', $owner);

        // Sanity: 5 units/KES × 0.18 KES/unit = 0.90 KES kept per KES 1. ✓
        $perKes = bcmul('5', $owner, 4);
        $this->assertSame('0.9000', $perKes);
    }

    public function test_price_per_unit(): void
    {
        $config = new ModuleTokenConfig(['units_per_kes' => '5']);
        $this->assertSame('0.2000', $config->pricePerUnit());
    }

    public function test_zero_units_per_kes_is_safe(): void
    {
        $this->assertSame('0.0000', ModuleTokenConfig::computeOwnerRevenuePerUnit('0', '0.02'));
    }

    public function test_higher_consumption_lower_commission(): void
    {
        // Gas: units_per_kes=4 (price 0.25/unit), commission 0.01 → owner 0.24.
        $this->assertSame('0.2400', ModuleTokenConfig::computeOwnerRevenuePerUnit('4', '0.01'));
    }
}
