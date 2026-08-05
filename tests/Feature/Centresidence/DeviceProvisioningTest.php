<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\Device;
use App\Centresidence\Models\Gateway;
use App\Centresidence\Models\InfrastructureTopology;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Services\ChirpStack\LiveChirpStackDriver;
use App\Centresidence\Services\DeviceProvisioningService;

/**
 * Layer-1 deployment: provisioning a funded module into the logical
 * infrastructure the billing engines read. Default ChirpStack driver is
 * 'simulated' (auto-activate) so the metered chain works without hardware.
 */
class DeviceProvisioningTest extends CentresidenceDatabaseTestCase
{
    public function test_metered_deploy_creates_full_billing_stack(): void
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true]);

        $pm = app(DeviceProvisioningService::class)->deploy(1, 1, $module, 2);

        $this->assertSame(PropertyModule::STATUS_ACTIVE, $pm->status);
        $this->assertSame('transaction', $pm->billing_model);     // owner 1 is transaction-mode
        $this->assertSame(2, (int) $pm->active_meter_count);
        $this->assertNotNull($pm->activated_at);

        // Gateway + topology so requires_gateway components bill.
        $this->assertSame(1, Gateway::count());
        $topology = InfrastructureTopology::where('property_id', 1)->where('status', 'active')->first();
        $this->assertNotNull($topology);
        $this->assertSame('100.00', $topology->allocation_percentage); // sole allocation

        // 2 active simulated devices on the gateway, with editable placeholder EUIs.
        $devices = $pm->devices()->get();
        $this->assertCount(2, $devices);
        $this->assertTrue($devices->every(fn ($d) => $d->status === Device::STATUS_ACTIVE));
        $this->assertTrue($devices->every(fn ($d) => $d->is_simulated));
        $this->assertTrue($devices->every(fn ($d) => str_starts_with($d->dev_eui, 'DEV-')));
        $this->assertTrue($devices->every(fn ($d) => $d->gateway_id === Gateway::first()->id));
    }

    public function test_free_owner_cannot_deploy_modules(): void
    {
        // Free plans have no rail to bill a module's recurring infra cost → blocked.
        \Illuminate\Support\Facades\DB::table('owner_packages')->where('user_id', 1)->update(['pricing_model' => 'free']);
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true]);

        $this->expectException(\App\Centresidence\Exceptions\ModuleDeploymentRequiresPaidPlanException::class);
        app(DeviceProvisioningService::class)->deploy(1, 1, $module, 1);
    }

    public function test_billing_model_follows_owner_pricing_mode(): void
    {
        // Owner 2 on subscription mode → modules are subscription-billed (cost
        // bundled in the plan), regardless of metered-ness.
        \Illuminate\Support\Facades\DB::table('owner_packages')->where('user_id', 2)->update(['pricing_model' => 'subscription']);
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true]);

        $pm = app(DeviceProvisioningService::class)->deploy(2, 2, $module, 1);

        $this->assertSame('subscription', $pm->billing_model);
    }

    public function test_non_metered_deploy_skips_gateway(): void
    {
        $module = Module::create(['key' => 'smart_lock', 'name' => 'Smart Lock', 'is_metered' => false]);

        $pm = app(DeviceProvisioningService::class)->deploy(1, 1, $module, 1);

        // Owner 1 is on transaction mode → billing routes to rent recovery.
        $this->assertSame('transaction', $pm->billing_model);
        $this->assertSame(1, (int) $pm->active_meter_count);
        $this->assertSame(0, Gateway::count());
        $this->assertSame(0, InfrastructureTopology::count());
        $this->assertNull($pm->devices()->first()->gateway_id);
    }

    public function test_deploy_is_idempotent_and_tops_up(): void
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true]);
        $service = app(DeviceProvisioningService::class);

        $service->deploy(1, 1, $module, 1);
        $service->deploy(1, 1, $module, 3);   // top up to 3
        $pm = $service->deploy(1, 1, $module, 2); // no reduction, no duplication

        $this->assertSame(3, $pm->devices()->count());
        $this->assertSame(3, (int) $pm->active_meter_count);
        $this->assertSame(1, Gateway::count());                    // gateway reused
        $this->assertSame(1, InfrastructureTopology::where('property_id', 1)->count());
    }

    public function test_devices_bind_to_a_selected_gateway(): void
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true]);
        $gateway = Gateway::create(['name' => 'Shared GW', 'status' => 'active']);

        $service = app(DeviceProvisioningService::class);
        $pm = $service->deploy(1, 1, $module, 2, $gateway->id);

        $this->assertSame(2, $service->lastProvisionedCount);          // accurate count
        $this->assertSame(1, Gateway::count());                        // no auto-gateway created
        $this->assertTrue($pm->devices()->get()->every(fn ($d) => $d->gateway_id === $gateway->id));
    }

    public function test_top_up_reports_only_newly_created_devices(): void
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true]);
        $service = app(DeviceProvisioningService::class);

        $service->deploy(1, 1, $module, 1);
        $this->assertSame(1, $service->lastProvisionedCount);

        $service->deploy(1, 1, $module, 3);   // adds 2
        $this->assertSame(2, $service->lastProvisionedCount);

        $service->deploy(1, 1, $module, 2);   // adds 0 (already at 3)
        $this->assertSame(0, $service->lastProvisionedCount);
    }

    public function test_gateway_capacity_is_enforced(): void
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true]);
        $gateway = Gateway::create(['name' => 'Tiny GW', 'status' => 'active', 'max_devices' => 1]);

        $this->expectException(\App\Centresidence\Exceptions\GatewayCapacityExceededException::class);
        app(DeviceProvisioningService::class)->deploy(1, 1, $module, 2, $gateway->id);
    }

    public function test_live_driver_leaves_devices_provisioning(): void
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true]);

        // Live driver: devices await the ChirpStack join, so they stay provisioning.
        $service = new DeviceProvisioningService(new LiveChirpStackDriver(), app(\App\Centresidence\Services\PaymentModeService::class));
        $pm = $service->deploy(1, 1, $module, 2);

        $devices = $pm->devices()->get();
        $this->assertTrue($devices->every(fn ($d) => $d->status === Device::STATUS_PROVISIONING));
        $this->assertTrue($devices->every(fn ($d) => ! $d->is_simulated));
        $this->assertSame(0, (int) $pm->active_meter_count);       // none active yet
    }
}
