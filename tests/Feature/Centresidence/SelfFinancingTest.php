<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\Module;
use App\Centresidence\Models\SelfFinancedModule;
use App\Centresidence\Services\SelfFinancingService;

/**
 * Self-financing: an owner funds a module deployment (hardware + installation)
 * themselves — no partner, no facility, no transaction-mode requirement.
 */
class SelfFinancingTest extends CentresidenceDatabaseTestCase
{
    public function test_creates_order_with_hardware_plus_installation(): void
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_financeable' => true]);
        $item = $module->pricingCatalogueItems()->create([
            'item_name' => 'Meter', 'unit_price' => 3500, 'installation_cost' => 500, 'unit_label' => 'meter',
        ]);

        $order = app(SelfFinancingService::class)->createOrder(1, 1, $item, 10);

        $this->assertSame('35000.00', $order->hardware_cost);   // 3500 × 10
        $this->assertSame('5000.00', $order->installation_cost); // 500 × 10
        $this->assertSame('40000.00', $order->total_cost);
        $this->assertSame(SelfFinancedModule::STATUS_PENDING_PAYMENT, $order->status);
        $this->assertStringStartsWith('SELF-', $order->reference);
    }

    public function test_mark_paid_and_deployed(): void
    {
        $module = Module::create(['key' => 'lock', 'name' => 'Smart Lock']);
        $item = $module->pricingCatalogueItems()->create(['item_name' => 'Lock', 'unit_price' => 2000, 'installation_cost' => 0]);
        $service = app(SelfFinancingService::class);

        $order = $service->createOrder(1, 1, $item, 3);
        $this->assertSame('6000.00', $order->total_cost);

        $service->markPaid($order);
        $this->assertSame(SelfFinancedModule::STATUS_PAID, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);

        $service->markDeployed($order);
        $this->assertSame(SelfFinancedModule::STATUS_DEPLOYED, $order->fresh()->status);
    }
}
