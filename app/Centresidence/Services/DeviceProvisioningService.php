<?php

namespace App\Centresidence\Services;

use App\Centresidence\Exceptions\GatewayCapacityExceededException;
use App\Centresidence\Exceptions\ModuleDeploymentRequiresPaidPlanException;
use App\Centresidence\Models\Device;
use App\Centresidence\Models\Gateway;
use App\Centresidence\Models\InfrastructureTopology;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Services\ChirpStack\ChirpStackDriver;
use App\Models\PropertyUnit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Layer 1 of deployment: turns a funded facility / paid self-financed order
 * into the LOGICAL infrastructure the billing engines read —
 *
 *   PropertyModule (activated, billing_model)            ← what the engines iterate
 *   + Gateway + InfrastructureTopology   (metered only)  ← gates requires_gateway costs
 *   + one Device per unit                                ← drives active_meter_count
 *
 * Layer 2 (binding to the physical LoRaWAN network) is delegated to the
 * driver-gated ChirpStackDriver: 'simulated' activates devices immediately so
 * the metered chain can be exercised without hardware; 'live' leaves them
 * provisioning until the ChirpStack uplink confirms the join.
 *
 * Idempotent: re-running tops the module up to `quantity` devices (never
 * duplicates) and is safe to call again after a partial deploy.
 */
class DeviceProvisioningService
{
    /** Number of devices actually created in the most recent deploy() call. */
    public int $lastProvisionedCount = 0;

    public function __construct(
        private ChirpStackDriver $chirpstack,
        private PaymentModeService $paymentMode
    ) {
    }

    /**
     * @param  int|null  $gatewayId  bind devices to this existing gateway; null
     *   auto-creates/reuses one shared gateway per property (metered modules only).
     */
    public function deploy(int $ownerId, int $propertyId, Module $module, int $quantity, ?int $gatewayId = null): PropertyModule
    {
        // A deployed module bills a recurring infra cost every month, so the
        // owner must be on a mode that can collect it (transaction → from rent,
        // subscription → on the plan invoice). Free has no rail → block here so
        // no path (admin, partner, self-finance) can create uncollectable charges.
        if (! $this->paymentMode->hasModuleBillingRail($ownerId)) {
            throw new ModuleDeploymentRequiresPaidPlanException($ownerId);
        }

        return DB::transaction(function () use ($ownerId, $propertyId, $module, $quantity, $gatewayId) {
            $propertyModule = $this->ensurePropertyModule($ownerId, $propertyId, $module);

            // Metered modules need a token config so tenants can buy units.
            if ($module->is_metered) {
                $this->ensureTokenConfig($propertyModule, $module);
            }

            $gateway = null;
            if ($this->needsGateway($module)) {
                $gateway = $gatewayId
                    ? Gateway::findOrFail($gatewayId)
                    : $this->ensureGateway($propertyId);
                $this->ensureTopology($ownerId, $propertyId, $gateway);
            }

            $this->lastProvisionedCount = $this->ensureDevices($propertyModule, $module, $gateway, $quantity);

            // active_meter_count drives per-active-device cost & commission.
            $propertyModule->forceFill([
                'active_meter_count' => $propertyModule->activeDevices()->count(),
            ])->save();

            return $propertyModule->refresh();
        });
    }

    private function ensurePropertyModule(int $ownerId, int $propertyId, Module $module): PropertyModule
    {
        $propertyModule = PropertyModule::firstOrCreate(
            ['property_id' => $propertyId, 'module_id' => $module->id, 'property_unit_id' => null],
            [
                'owner_id'      => $ownerId,
                // Recovery routes by OWNER pricing mode: transaction owners pay
                // module infra costs from rent; subscription owners in their plan.
                'billing_model' => $this->paymentMode->isTransactionMode($ownerId)
                    ? PropertyModule::BILLING_TRANSACTION
                    : PropertyModule::BILLING_SUBSCRIPTION,
                'status'        => PropertyModule::STATUS_ACTIVE,
                'activated_at'  => Carbon::now(),
            ]
        );

        if ($propertyModule->status !== PropertyModule::STATUS_ACTIVE) {
            $propertyModule->forceFill([
                'status'       => PropertyModule::STATUS_ACTIVE,
                'activated_at' => $propertyModule->activated_at ?? Carbon::now(),
                'deactivated_at' => null,
            ])->save();
        }

        return $propertyModule;
    }

    /** Create the token config from the module's economics defaults (idempotent). */
    private function ensureTokenConfig(PropertyModule $propertyModule, Module $module): void
    {
        if ($propertyModule->tokenConfig) {
            return;
        }

        $propertyModule->tokenConfig()->create([
            'token_unit_label' => $module->token_unit_label ?: 'Units',
            'units_per_kes' => $module->token_units_per_kes ?: 1,
            // Commission is the Centresidence income share — 0 by default; set
            // per module (e.g. gas) by the admin.
            'centresidence_commission_per_token_unit' => $module->token_commission_per_unit ?: 0,
            'is_active' => true,
        ]);
    }

    private function needsGateway(Module $module): bool
    {
        return (bool) $module->is_metered
            || $module->costComponents()->where('requires_gateway', true)->exists();
    }

    /** One shared gateway per property; reuse the topology's gateway if present. */
    private function ensureGateway(int $propertyId): Gateway
    {
        $topology = InfrastructureTopology::query()
            ->where('infrastructure_type', 'gateway')
            ->where('property_id', $propertyId)
            ->where('status', 'active')
            ->first();

        if ($topology && ($gateway = Gateway::find($topology->infrastructure_id))) {
            return $gateway;
        }

        $gateway = Gateway::create([
            'eui'          => $this->placeholderEui('GW'),
            'name'         => 'Gateway · Property #' . $propertyId,
            'status'       => 'active',
            'is_simulated' => $this->chirpstack->autoActivates(),
        ]);

        $this->chirpstack->registerGateway($gateway);

        return $gateway;
    }

    /** Ensure an active topology row so requires_gateway components bill. */
    private function ensureTopology(int $ownerId, int $propertyId, Gateway $gateway): void
    {
        $exists = InfrastructureTopology::query()
            ->where('infrastructure_type', 'gateway')
            ->where('infrastructure_id', $gateway->id)
            ->where('property_id', $propertyId)
            ->where('status', 'active')
            ->exists();

        if ($exists) {
            return;
        }

        $today = Carbon::now()->toDateString();
        // Take the remaining share of the gateway (informational under the
        // per-device model, but kept ≤ 100% by the allocation invariant).
        $remaining = 100.0 - InfrastructureTopology::totalAllocationFor('gateway', $gateway->id, $today);
        $allocation = max(0.0, min(100.0, $remaining));

        InfrastructureTopology::assertValidAllocation('gateway', $gateway->id, $today, $allocation);

        InfrastructureTopology::create([
            'infrastructure_type'   => 'gateway',
            'infrastructure_id'     => $gateway->id,
            'owner_id'              => $ownerId,
            'property_id'           => $propertyId,
            'allocation_percentage' => $allocation,
            'billing_model'         => 'per_active_device_uncapped',
            'monthly_base_cost'     => 0, // informational; billing is per-device
            'status'                => 'active',
            'effective_from'        => $today,
        ]);
    }

    /**
     * Top the module up to `quantity` devices, mapped one-per-unit.
     * Returns the number of devices actually created.
     */
    private function ensureDevices(PropertyModule $propertyModule, Module $module, ?Gateway $gateway, int $quantity): int
    {
        $existing = $propertyModule->devices()->count();
        if ($existing >= $quantity) {
            return 0;
        }

        $toCreate = $quantity - $existing;

        // Capacity guard: a gateway with a max_devices cap cannot be overfilled.
        if ($gateway && $gateway->max_devices) {
            $onGateway = Device::where('gateway_id', $gateway->id)->whereNull('deleted_at')->count();
            if ($onGateway + $toCreate > (int) $gateway->max_devices) {
                throw new GatewayCapacityExceededException(
                    "Gateway '{$gateway->name}' holds {$onGateway}/{$gateway->max_devices} devices; "
                    . "cannot add {$toCreate} more."
                );
            }
        }

        $unitIds = PropertyUnit::where('property_id', $propertyModule->property_id)
            ->orderBy('id')->pluck('id')->all();
        $autoActivate = $this->chirpstack->autoActivates();

        for ($i = $existing; $i < $quantity; $i++) {
            $device = $propertyModule->devices()->create([
                'name'             => $module->name . ' Unit ' . ($i + 1),
                'gateway_id'       => $gateway?->id,
                'dev_eui'          => $this->placeholderEui('DEV'),
                'status'           => Device::STATUS_PROVISIONING,
                'is_simulated'     => $autoActivate,
                'property_unit_id' => $unitIds[$i] ?? null, // authoritative device→unit link (drives wallet attribution)
            ]);

            $this->chirpstack->registerDevice($device);

            if ($autoActivate) {
                $device->forceFill([
                    'status'       => Device::STATUS_ACTIVE,
                    'activated_at' => Carbon::now(),
                    'last_seen_at' => Carbon::now(),
                ])->save();
            }
        }

        return $toCreate;
    }

    /** Unique editable placeholder EUI (installer replaces with the real one). */
    private function placeholderEui(string $prefix): string
    {
        return $prefix . '-' . strtoupper(Str::random(12));
    }
}
