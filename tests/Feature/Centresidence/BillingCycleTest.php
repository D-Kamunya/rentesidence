<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Events\BillingCycleStarted;
use App\Centresidence\Events\CommissionInvoiceGenerated;
use App\Centresidence\Events\InfrastructureInvoiceGenerated;
use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Centresidence\Models\Gateway;
use App\Centresidence\Models\InfrastructureTopology;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\ModuleCostComponent;
use App\Centresidence\Models\OwnerInfrastructureInvoice;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Services\BillingCycleService;
use App\Centresidence\Services\CommissionEngine;
use App\Centresidence\Services\InfrastructureCostEngine;
use App\Centresidence\Support\Money;
use App\Models\Property;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

/**
 * WP3 — Infrastructure Cost + Commission engines, validated against the
 * handbook's own Simulation Success Criteria (§19 Test Cases 1 & 2). These are
 * the acceptance tests the handbook says must pass before Phase 3.
 */
class BillingCycleTest extends CentresidenceDatabaseTestCase
{
    private Carbon $month;

    protected function setUp(): void
    {
        parent::setUp();
        $this->month = Carbon::parse('2026-06-01'); // 30-day month, full proration
    }

    // ── Fixtures ──────────────────────────────────────────────────────────

    /** Water meter: metered, platform fee 50 + gateway usage 50 (both fallback-eligible). */
    private function waterModule(): Module
    {
        $m = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true, 'token_unit_label' => 'Litres']);
        $m->costComponents()->createMany([
            ['component_name' => 'platform_software_fee', 'cost_model' => ModuleCostComponent::COST_MODEL_PER_ACTIVE_DEVICE, 'rate' => 50, 'requires_gateway' => false, 'is_fallback_eligible' => true, 'display_order' => 1],
            ['component_name' => 'lorawan_gateway_usage', 'cost_model' => ModuleCostComponent::COST_MODEL_PER_ACTIVE_DEVICE, 'rate' => 50, 'requires_gateway' => true, 'is_fallback_eligible' => true, 'display_order' => 2],
        ]);

        return $m;
    }

    /** Smart lock: non-metered, gateway usage 75 (never fallback-eligible). */
    private function lockModule(): Module
    {
        $m = Module::create(['key' => 'smart_lock', 'name' => 'Smart Lock', 'is_metered' => false]);
        $m->costComponents()->create(
            ['component_name' => 'lorawan_gateway_usage', 'cost_model' => ModuleCostComponent::COST_MODEL_PER_ACTIVE_DEVICE, 'rate' => 75, 'requires_gateway' => true, 'is_fallback_eligible' => false, 'display_order' => 1]
        );

        return $m;
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

    private function topology(int $gatewayId, int $ownerId, int $propertyId, float $allocation, float $base): InfrastructureTopology
    {
        return InfrastructureTopology::create([
            'infrastructure_type' => InfrastructureTopology::TYPE_GATEWAY,
            'infrastructure_id' => $gatewayId,
            'owner_id' => $ownerId,
            'property_id' => $propertyId,
            'allocation_percentage' => $allocation,
            'billing_model' => InfrastructureTopology::BILLING_PER_DEVICE_UNCAPPED,
            'monthly_base_cost' => $base,
            'effective_from' => '2026-06-01',
        ]);
    }

    // ── Handbook §19 Test Case 1 ──────────────────────────────────────────

    public function test_case_1_single_owner_subscription_invoice_is_12375(): void
    {
        $gw = Gateway::create(['name' => 'GW-1', 'is_simulated' => true]);
        $water = $this->waterModule();
        $lock = $this->lockModule();

        $this->propertyModule(1, 1, $water, 20, PropertyModule::BILLING_SUBSCRIPTION);
        $this->propertyModule(1, 1, $lock, 5, PropertyModule::BILLING_SUBSCRIPTION);
        $this->topology($gw->id, 1, 1, 100.00, 5000.00);

        $invoice = app(CommissionEngine::class)->generateForProperty(
            Property::find(1),
            $this->month,
            Money::fromDecimal('10000.00') // base subscription
        );

        $this->assertNotNull($invoice);
        // Water: 20 × (50 + 50) = 2,000 (metered).
        $this->assertSame('2000.00', $invoice->metered_commission_total);
        // Locks: 5 × 75 = 375 (non-metered).
        $this->assertSame('375.00', $invoice->non_metered_commission_total);
        // Total = 10,000 + 2,000 + 375 = 12,375.
        $this->assertSame('12375.00', $invoice->total_amount);

        // Breakdown captured for transparency.
        $this->assertCount(2, $invoice->metered_commission_breakdown);
        $this->assertCount(1, $invoice->non_metered_commission_breakdown);
        $this->assertCount(1, $invoice->infrastructure_cost_breakdown);
    }

    public function test_gateway_components_are_skipped_without_topology(): void
    {
        $water = $this->waterModule();
        $this->propertyModule(1, 1, $water, 20, PropertyModule::BILLING_SUBSCRIPTION);
        // No gateway / topology → lorawan_gateway_usage must NOT be charged.

        $invoice = app(CommissionEngine::class)->generateForProperty(
            Property::find(1),
            $this->month,
            Money::zero()
        );

        // Only platform_software_fee (no gateway required): 20 × 50 = 1,000.
        $this->assertSame('1000.00', $invoice->metered_commission_total);
        $this->assertCount(1, $invoice->metered_commission_breakdown);
    }

    // ── Handbook §19 Test Case 2 ──────────────────────────────────────────

    public function test_case_2_multi_owner_mixed_billing_models(): void
    {
        $gw = Gateway::create(['name' => 'Shared Building GW', 'is_simulated' => true]);
        $water = $this->waterModule();
        $lock = $this->lockModule();

        // Owner A — subscription, 60% allocation.
        $this->propertyModule(1, 1, $water, 12, PropertyModule::BILLING_SUBSCRIPTION);
        $this->propertyModule(1, 1, $lock, 3, PropertyModule::BILLING_SUBSCRIPTION);
        $this->topology($gw->id, 1, 1, 60.00, 10000.00);

        // Owner B — transaction, 40% allocation.
        $this->propertyModule(2, 2, $water, 8, PropertyModule::BILLING_TRANSACTION);
        $this->propertyModule(2, 2, $lock, 2, PropertyModule::BILLING_TRANSACTION);
        $this->topology($gw->id, 2, 2, 40.00, 10000.00);

        $commissionEngine = app(CommissionEngine::class);
        $infraEngine = app(InfrastructureCostEngine::class);

        // Owner A commission invoice: 12 × 100 water (metered) + 3 × 75 locks.
        $aInvoice = $commissionEngine->generateForProperty(Property::find(1), $this->month, Money::fromDecimal('10000.00'));
        $this->assertSame('1200.00', $aInvoice->metered_commission_total);
        $this->assertSame('225.00', $aInvoice->non_metered_commission_total);
        // Topology context records the 60% share (6,000) but does not bill it.
        $this->assertSame('6000.00', $aInvoice->infrastructure_cost_breakdown[0]['owner_share']);

        // Owner A has NO separate infra invoice (subscription model).
        $this->assertNull($infraEngine->generateForProperty(Property::find(1), $this->month));

        // Owner B (transaction): infra invoice now bills ALL modules' software +
        // gateway — water 8 × (50+50) = 800 + locks 2 × 75 = 150 → 950 across 3
        // component lines. (Metered per-token commission is separate; gas only.)
        $bInfra = $infraEngine->generateForProperty(Property::find(2), $this->month);
        $this->assertNotNull($bInfra);
        $this->assertSame('950.00', $bInfra->total_amount);
        $this->assertCount(3, $bInfra->breakdown_json);

        // Owner B gets no commission invoice (no subscription modules, no base).
        $this->assertNull($commissionEngine->generateForProperty(Property::find(2), $this->month, Money::zero()));
    }

    // ── Orchestration + idempotency ───────────────────────────────────────

    public function test_billing_cycle_service_runs_both_engines_and_fires_events(): void
    {
        Event::fake([BillingCycleStarted::class, CommissionInvoiceGenerated::class, InfrastructureInvoiceGenerated::class]);

        $gw = Gateway::create(['name' => 'GW', 'is_simulated' => true]);
        $water = $this->waterModule();
        $lock = $this->lockModule();

        $this->propertyModule(1, 1, $water, 20, PropertyModule::BILLING_SUBSCRIPTION);
        $this->propertyModule(1, 1, $lock, 5, PropertyModule::BILLING_SUBSCRIPTION);
        $this->topology($gw->id, 1, 1, 100.00, 5000.00);

        $this->propertyModule(2, 2, $lock, 2, PropertyModule::BILLING_TRANSACTION);
        $this->topology($gw->id, 2, 2, 100.00, 5000.00);

        $summary = app(BillingCycleService::class)->runForMonth(
            $this->month,
            fn (Property $p) => $p->id === 1 ? Money::fromDecimal('10000.00') : Money::zero()
        );

        $this->assertSame(1, $summary['commission_invoices']);
        $this->assertSame(1, $summary['infrastructure_invoices']);

        Event::assertDispatched(BillingCycleStarted::class);
        Event::assertDispatched(CommissionInvoiceGenerated::class);
        Event::assertDispatched(InfrastructureInvoiceGenerated::class);
    }

    public function test_billing_cycle_is_idempotent(): void
    {
        $gw = Gateway::create(['name' => 'GW', 'is_simulated' => true]);
        $water = $this->waterModule();
        $this->propertyModule(1, 1, $water, 20, PropertyModule::BILLING_SUBSCRIPTION);
        $this->topology($gw->id, 1, 1, 100.00, 5000.00);

        $resolver = fn (Property $p) => Money::fromDecimal('10000.00');
        $service = app(BillingCycleService::class);

        $service->runForMonth($this->month, $resolver);
        $service->runForMonth($this->month, $resolver); // re-run same cycle

        // Exactly one invoice for the (owner, property, month), updated not dup'd.
        $this->assertSame(1, CentresidenceCommissionInvoice::where('property_id', 1)
            ->whereDate('billing_month', '2026-06-01')->count());
    }
}
