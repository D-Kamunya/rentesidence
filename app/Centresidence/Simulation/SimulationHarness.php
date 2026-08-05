<?php

namespace App\Centresidence\Simulation;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Centresidence\Models\Gateway;
use App\Centresidence\Models\InfrastructureTopology;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\ModuleCostComponent;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Services\CommissionEngine;
use App\Centresidence\Services\CommissionFallbackService;
use App\Centresidence\Services\InfrastructureCostEngine;
use App\Centresidence\Services\TokenEngine;
use App\Centresidence\Support\Money;
use App\Models\Property;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * End-to-end simulation of the Centresidence infrastructure + commission half,
 * driving the REAL engines through the handbook's four §19 Simulation Success
 * Criteria. Assumes a freshly-booted Sandbox (in-memory). Returns a structured
 * pass/fail report — consumed by both the `centresidence:simulate` command and
 * the official gate test.
 *
 * This is the Phase 1 → Phase 3 gate: if every criterion passes, the multi-
 * owner topology, composable commission, token economics and tenant-protective
 * fallback are proven before any finance/hardware work.
 */
class SimulationHarness
{
    private Module $water;
    private Module $lock;
    private Carbon $month;

    public function __construct()
    {
        $this->month = Carbon::parse('2026-06-01');
    }

    /** Seed actors, build shared modules, and run all four criteria. */
    public function runAll(): array
    {
        $this->seedActors();
        $this->buildModules();

        $cases = [
            $this->case1SingleOwnerSubscription(),
            $this->case2MultiOwnerMixedBilling(),
            $this->case3FallbackExecution(),
            $this->case4TokenEconomics(),
        ];

        return [
            'cases' => $cases,
            'all_pass' => collect($cases)->every(fn ($c) => $c['pass']),
        ];
    }

    // ── Fixtures ──────────────────────────────────────────────────────────

    private function seedActors(): void
    {
        DB::table('users')->insert([
            ['id' => 1, 'first_name' => 'Owner', 'last_name' => 'One'],
            ['id' => 2, 'first_name' => 'Owner', 'last_name' => 'A'],
            ['id' => 3, 'first_name' => 'Owner', 'last_name' => 'B'],
            ['id' => 4, 'first_name' => 'Owner', 'last_name' => 'Four'],
            ['id' => 5, 'first_name' => 'Owner', 'last_name' => 'Five'],
            ['id' => 14, 'first_name' => 'Tenant', 'last_name' => 'C3'],
            ['id' => 15, 'first_name' => 'Tenant', 'last_name' => 'C4'],
        ]);
        DB::table('properties')->insert([
            ['id' => 1, 'owner_user_id' => 1, 'name' => 'Standalone Apartment'],
            ['id' => 2, 'owner_user_id' => 2, 'name' => 'Building Floors 1-6'],
            ['id' => 3, 'owner_user_id' => 3, 'name' => 'Building Floors 7-10'],
            ['id' => 4, 'owner_user_id' => 4, 'name' => 'Defaulting Property'],
            ['id' => 5, 'owner_user_id' => 5, 'name' => 'Token Property'],
        ]);
        DB::table('property_units')->insert([
            ['id' => 1, 'property_id' => 1, 'name' => 'Unit'],
            ['id' => 2, 'property_id' => 2, 'name' => 'Unit'],
            ['id' => 3, 'property_id' => 3, 'name' => 'Unit'],
            ['id' => 4, 'property_id' => 4, 'name' => 'Unit'],
            ['id' => 5, 'property_id' => 5, 'name' => 'Unit'],
        ]);
    }

    private function buildModules(): void
    {
        $this->water = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true, 'token_unit_label' => 'Litres']);
        $this->water->costComponents()->createMany([
            ['component_name' => 'platform_software_fee', 'cost_model' => ModuleCostComponent::COST_MODEL_PER_ACTIVE_DEVICE, 'rate' => 50, 'requires_gateway' => false, 'is_fallback_eligible' => true, 'display_order' => 1],
            ['component_name' => 'lorawan_gateway_usage', 'cost_model' => ModuleCostComponent::COST_MODEL_PER_ACTIVE_DEVICE, 'rate' => 50, 'requires_gateway' => true, 'is_fallback_eligible' => true, 'display_order' => 2],
        ]);

        $this->lock = Module::create(['key' => 'smart_lock', 'name' => 'Smart Lock', 'is_metered' => false]);
        $this->lock->costComponents()->create(
            ['component_name' => 'lorawan_gateway_usage', 'cost_model' => ModuleCostComponent::COST_MODEL_PER_ACTIVE_DEVICE, 'rate' => 75, 'requires_gateway' => true, 'is_fallback_eligible' => false, 'display_order' => 1]
        );
    }

    private function propertyModule(int $propertyId, int $ownerId, Module $module, int $count, string $billing): PropertyModule
    {
        return PropertyModule::create([
            'property_id' => $propertyId,
            'module_id' => $module->id,
            'owner_id' => $ownerId,
            'active_meter_count' => $count,
            'billing_model' => $billing,
            'status' => PropertyModule::STATUS_ACTIVE,
        ]);
    }

    private function tokenConfig(PropertyModule $pm): void
    {
        $pm->tokenConfig()->create([
            'token_unit_label' => 'Litres',
            'units_per_kes' => '5',
            'centresidence_commission_per_token_unit' => '0.02',
        ]);
    }

    private function topology(int $gatewayId, int $ownerId, int $propertyId, float $alloc, float $base): void
    {
        InfrastructureTopology::create([
            'infrastructure_type' => InfrastructureTopology::TYPE_GATEWAY,
            'infrastructure_id' => $gatewayId,
            'owner_id' => $ownerId,
            'property_id' => $propertyId,
            'allocation_percentage' => $alloc,
            'billing_model' => InfrastructureTopology::BILLING_PER_DEVICE_UNCAPPED,
            'monthly_base_cost' => $base,
            'effective_from' => '2026-06-01',
        ]);
    }

    private function check(string $name, string $expected, string $actual): array
    {
        return ['name' => $name, 'expected' => $expected, 'actual' => $actual, 'pass' => $expected === $actual];
    }

    private function result(string $key, string $title, array $checks): array
    {
        return ['key' => $key, 'title' => $title, 'checks' => $checks, 'pass' => collect($checks)->every(fn ($c) => $c['pass'])];
    }

    // ── Criteria ──────────────────────────────────────────────────────────

    private function case1SingleOwnerSubscription(): array
    {
        $gw = Gateway::create(['name' => 'GW-1', 'is_simulated' => true]);
        $this->propertyModule(1, 1, $this->water, 20, PropertyModule::BILLING_SUBSCRIPTION);
        $this->propertyModule(1, 1, $this->lock, 5, PropertyModule::BILLING_SUBSCRIPTION);
        $this->topology($gw->id, 1, 1, 100.00, 5000.00);

        $invoice = app(CommissionEngine::class)->generateForProperty(Property::find(1), $this->month, Money::fromDecimal('10000.00'));

        return $this->result('TC1', 'Single Owner, Subscription Model', [
            $this->check('Water metered commission (20 × 100)', '2000.00', $invoice->metered_commission_total),
            $this->check('Smart locks non-metered (5 × 75)', '375.00', $invoice->non_metered_commission_total),
            $this->check('Total invoice (10,000 + 2,000 + 375)', '12375.00', $invoice->total_amount),
        ]);
    }

    private function case2MultiOwnerMixedBilling(): array
    {
        $gw = Gateway::create(['name' => 'Shared Building GW', 'is_simulated' => true]);

        // Owner A — subscription, 60%.
        $this->propertyModule(2, 2, $this->water, 12, PropertyModule::BILLING_SUBSCRIPTION);
        $this->propertyModule(2, 2, $this->lock, 3, PropertyModule::BILLING_SUBSCRIPTION);
        $this->topology($gw->id, 2, 2, 60.00, 10000.00);

        // Owner B — transaction, 40%.
        $this->propertyModule(3, 3, $this->water, 8, PropertyModule::BILLING_TRANSACTION);
        $this->propertyModule(3, 3, $this->lock, 2, PropertyModule::BILLING_TRANSACTION);
        $this->topology($gw->id, 3, 3, 40.00, 10000.00);

        $aInvoice = app(CommissionEngine::class)->generateForProperty(Property::find(2), $this->month, Money::fromDecimal('10000.00'));
        $bInfra = app(InfrastructureCostEngine::class)->generateForProperty(Property::find(3), $this->month);

        return $this->result('TC2', 'Multi-Owner Gateway, Mixed Billing Models', [
            $this->check('Owner A water metered (12 × 100)', '1200.00', $aInvoice->metered_commission_total),
            $this->check('Owner A locks non-metered (3 × 75)', '225.00', $aInvoice->non_metered_commission_total),
            $this->check('Owner A gateway 60% allocation context', '6000.00', $aInvoice->infrastructure_cost_breakdown[0]['owner_share']),
            $this->check('Owner B infra invoice — all modules (water 8×100 + locks 2×75)', '950.00', $bInfra->total_amount),
        ]);
    }

    private function case3FallbackExecution(): array
    {
        $pm = $this->propertyModule(4, 4, $this->water, 20, PropertyModule::BILLING_SUBSCRIPTION);
        $this->tokenConfig($pm);
        $pm = $pm->fresh('tokenConfig');

        // Overdue invoice: metered 2,000 + non-metered (locks) 375.
        $invoice = CentresidenceCommissionInvoice::create([
            'owner_id' => 4, 'property_id' => 4, 'billing_month' => Carbon::parse('2026-05-01'),
            'metered_commission_total' => 2000, 'non_metered_commission_total' => 375,
            'total_amount' => 12375, 'status' => CentresidenceCommissionInvoice::STATUS_PENDING,
        ]);
        $activated = app(CommissionFallbackService::class)->activateOverdue(Carbon::parse('2026-06-01'));

        // Tenant buys KES 2,500 of tokens → fallback recovers metered only.
        $purchase = app(TokenEngine::class)->purchase($pm, 14, Money::fromDecimal('2500.00'));
        $invoice->refresh();

        return $this->result('TC3', 'Fallback Execution (tenant-protective)', [
            $this->check('Fallback activated', '1', (string) $activated),
            $this->check('Metered recovered from owner revenue', '2000.00', $purchase->fallback_deducted),
            $this->check('Metered marked cleared', '2000.00', $invoice->metered_paid_total),
            $this->check('Locks (non-metered) UNTOUCHED', '375.00', $invoice->non_metered_commission_total),
            $this->check('Invoice status partially_paid', 'partially_paid', $invoice->status),
            $this->check('Tenant received full units', '12500.0000', $purchase->units),
        ]);
    }

    private function case4TokenEconomics(): array
    {
        $pm = $this->propertyModule(5, 5, $this->water, 20, PropertyModule::BILLING_SUBSCRIPTION);
        $this->tokenConfig($pm);
        $pm = $pm->fresh('tokenConfig');

        $purchase = app(TokenEngine::class)->purchase($pm, 15, Money::fromDecimal('100.00'));

        return $this->result('TC4', 'Token Economics Integrity', [
            $this->check('Tenant receives 500 litres', '500.0000', $purchase->units),
            $this->check('Centresidence commission KES 10', '10.00', $purchase->centresidence_commission),
            $this->check('Owner revenue KES 90', '90.00', $purchase->owner_revenue_gross),
            $this->check('No fallback / extra fee', '0.00', $purchase->fallback_deducted),
        ]);
    }
}
