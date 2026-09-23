<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\Module;
use App\Centresidence\Models\ModuleCostComponent;
use App\Centresidence\Models\ModulePlatformFeeConfig;
use App\Centresidence\Models\ModulePricingCatalogueItem;
use App\Centresidence\Models\ModuleTokenConfig;
use App\Centresidence\Models\PropertyModule;

/**
 * Exercises the WP1 migrations + models end-to-end on an isolated in-memory
 * sqlite database (see CentresidenceDatabaseTestCase). Never touches the real
 * database or the 169 legacy migrations.
 */
class ModuleCatalogueTest extends CentresidenceDatabaseTestCase
{
    public function test_module_with_composable_cost_components(): void
    {
        $water = Module::create([
            'key' => 'water_meter',
            'name' => 'Water Meter',
            'is_metered' => true,
            'requires_gateway' => true,
            'token_unit_label' => 'Litres',
        ]);

        $water->costComponents()->createMany([
            [
                'component_name' => 'platform_software_fee',
                'cost_model' => ModuleCostComponent::COST_MODEL_PER_ACTIVE_DEVICE,
                'rate' => 50,
                'requires_gateway' => false,
                'is_fallback_eligible' => true,
                'display_order' => 1,
            ],
            [
                'component_name' => 'lorawan_gateway_usage',
                'cost_model' => ModuleCostComponent::COST_MODEL_PER_ACTIVE_DEVICE,
                'rate' => 50,
                'requires_gateway' => true,
                'is_fallback_eligible' => true,
                'display_order' => 2,
            ],
        ]);

        $this->assertCount(2, $water->activeCostComponents()->get());

        // 20 active meters × (50 + 50) = KES 2,000 for the module.
        $total = $water->activeCostComponents->reduce(
            fn ($carry, $c) => $carry->plus($c->perDeviceCost(20)),
            \App\Centresidence\Support\Money::zero()
        );
        $this->assertSame('2000.00', $total->toDecimal());
    }

    public function test_non_metered_module_is_not_fallback_capable(): void
    {
        $lock = Module::create([
            'key' => 'smart_lock',
            'name' => 'Smart Lock',
            'is_metered' => false,
            'requires_gateway' => true,
        ]);

        $this->assertFalse($lock->isFallbackCapable());
    }

    public function test_pricing_catalogue_base_cost(): void
    {
        $module = Module::create(['key' => 'm', 'name' => 'M', 'is_financeable' => true]);
        $item = $module->pricingCatalogueItems()->create([
            'item_name' => 'Water Meter Unit',
            'unit_price' => 3500.00,
            'unit_label' => 'meter',
        ]);

        $this->assertSame('35000.00', $item->baseCost(10)->toDecimal());
    }

    public function test_platform_fee_config_persists(): void
    {
        $module = Module::create(['key' => 'm2', 'name' => 'M2', 'is_financeable' => true]);
        $module->platformFeeConfigs()->create(['fee_percentage' => 10.00]);

        $this->assertEqualsWithDelta(10.00, (float) $module->platformFeeConfigs()->first()->fee_percentage, 0.001);
    }

    public function test_token_config_save_hook_derives_owner_revenue(): void
    {
        $module = Module::create(['key' => 'water', 'name' => 'Water', 'is_metered' => true]);
        $pm = PropertyModule::create([
            'property_id' => 1,
            'property_unit_id' => 1,
            'module_id' => $module->id,
            'owner_id' => 1,
            'active_meter_count' => 20,
            'billing_model' => PropertyModule::BILLING_SUBSCRIPTION,
            'status' => PropertyModule::STATUS_ACTIVE,
        ]);

        $config = $pm->tokenConfig()->create([
            'token_unit_label' => 'Litres',
            'units_per_kes' => 5,
            'centresidence_commission_per_token_unit' => 0.02,
            // owner_revenue intentionally omitted — the save hook derives it.
        ]);

        // Corrected formula: (1/5) - 0.02 = 0.18.
        $this->assertEqualsWithDelta(0.18, (float) $config->owner_revenue_per_token_unit, 0.0001);

        // Relationship integrity.
        $this->assertSame(1, $pm->owner->id);
        $this->assertSame('water', $pm->module->key);
        $this->assertTrue($pm->isMetered());
    }
}
