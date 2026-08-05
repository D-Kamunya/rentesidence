<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\Device;
use App\Centresidence\Models\Gateway;
use App\Centresidence\Models\InfrastructureTopology;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\PropertyModule;

/**
 * WP2 — device/gateway registry + infrastructure_topology. Proves the cost
 * allocation math from handbook §4 (the billing source of truth) and the
 * active-device counting that drives commission.
 */
class InfrastructureTopologyTest extends CentresidenceDatabaseTestCase
{
    private function gateway(array $attrs = []): Gateway
    {
        return Gateway::create(array_merge([
            'name' => 'GW-1',
            'status' => Gateway::STATUS_ACTIVE,
            'is_simulated' => true,
        ], $attrs));
    }

    /** Handbook §4.2 Scenario A: 1 gateway → 1 owner → 1 property, 100%. */
    public function test_simple_single_owner_allocation(): void
    {
        $gw = $this->gateway();

        $topology = InfrastructureTopology::create([
            'infrastructure_type' => InfrastructureTopology::TYPE_GATEWAY,
            'infrastructure_id' => $gw->id,
            'owner_id' => 1,
            'property_id' => 1,
            'allocation_percentage' => 100.00,
            'billing_model' => InfrastructureTopology::BILLING_PER_DEVICE_UNCAPPED,
            'monthly_base_cost' => 5000.00,
            'effective_from' => '2026-06-01',
        ]);

        $this->assertSame('5000.00', $topology->ownerShare()->toDecimal());
        $this->assertTrue($topology->asset()->is($gw));
    }

    /** Handbook §4.2 Scenario B: 1 gateway → 2 owners, 60% / 40% of KES 10,000. */
    public function test_multi_owner_gateway_allocation_sums_to_total(): void
    {
        $gw = $this->gateway(['name' => 'Shared Building GW']);

        $ownerA = InfrastructureTopology::create([
            'infrastructure_type' => InfrastructureTopology::TYPE_GATEWAY,
            'infrastructure_id' => $gw->id,
            'owner_id' => 1,
            'property_id' => 1,
            'allocation_percentage' => 60.00,
            'billing_model' => InfrastructureTopology::BILLING_PER_DEVICE_UNCAPPED,
            'monthly_base_cost' => 10000.00,
            'effective_from' => '2026-06-01',
        ]);

        $ownerB = InfrastructureTopology::create([
            'infrastructure_type' => InfrastructureTopology::TYPE_GATEWAY,
            'infrastructure_id' => $gw->id,
            'owner_id' => 2,
            'property_id' => 2,
            'allocation_percentage' => 40.00,
            'billing_model' => InfrastructureTopology::BILLING_PER_DEVICE_UNCAPPED,
            'monthly_base_cost' => 10000.00,
            'effective_from' => '2026-06-01',
        ]);

        $this->assertSame('6000.00', $ownerA->ownerShare()->toDecimal());
        $this->assertSame('4000.00', $ownerB->ownerShare()->toDecimal());

        // The two shares reconcile to the gateway's total cost.
        $total = $ownerA->ownerShare()->plus($ownerB->ownerShare());
        $this->assertSame('10000.00', $total->toDecimal());

        // Gateway sees both allocations.
        $this->assertCount(2, $gw->topologyAllocations()->get());
    }

    public function test_effective_on_scope_filters_by_date_window(): void
    {
        $gw = $this->gateway();
        $base = [
            'infrastructure_type' => InfrastructureTopology::TYPE_GATEWAY,
            'infrastructure_id' => $gw->id,
            'owner_id' => 1,
            'property_id' => 1,
            'allocation_percentage' => 100,
            'monthly_base_cost' => 5000,
        ];
        InfrastructureTopology::create($base + ['effective_from' => '2026-01-01', 'effective_to' => '2026-03-31']);
        $current = InfrastructureTopology::create($base + ['effective_from' => '2026-04-01']);

        $effective = InfrastructureTopology::query()->effectiveOn('2026-06-07')->get();

        $this->assertCount(1, $effective);
        $this->assertTrue($effective->first()->is($current));
    }

    public function test_active_meter_count_sync_from_devices(): void
    {
        $module = Module::create(['key' => 'water', 'name' => 'Water', 'is_metered' => true]);
        $pm = PropertyModule::create([
            'property_id' => 1,
            'module_id' => $module->id,
            'owner_id' => 1,
            'active_meter_count' => 0,
            'billing_model' => PropertyModule::BILLING_SUBSCRIPTION,
            'status' => PropertyModule::STATUS_ACTIVE,
        ]);
        $gw = $this->gateway();

        // 3 active devices + 1 inactive on this module.
        foreach (range(1, 3) as $i) {
            Device::create([
                'dev_eui' => "EUI-A-$i",
                'property_module_id' => $pm->id,
                'gateway_id' => $gw->id,
                'status' => Device::STATUS_ACTIVE,
                'is_simulated' => true,
            ]);
        }
        Device::create([
            'dev_eui' => 'EUI-A-inactive',
            'property_module_id' => $pm->id,
            'gateway_id' => $gw->id,
            'status' => Device::STATUS_INACTIVE,
            'is_simulated' => true,
        ]);

        $this->assertSame(3, $pm->syncActiveMeterCount());
        $this->assertSame(3, $pm->fresh()->active_meter_count);
        $this->assertCount(3, $gw->activeDevices()->get());
    }
}
