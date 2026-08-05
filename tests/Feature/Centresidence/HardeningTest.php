<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Exceptions\AllocationExceededException;
use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Centresidence\Models\Gateway;
use App\Centresidence\Models\InfrastructureTopology;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\ModuleCostComponent;
use App\Centresidence\Models\PartnerRemittanceBatch;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\TokenPurchase;
use App\Centresidence\Models\UtilityWallet;
use App\Centresidence\Services\CommissionEngine;
use App\Centresidence\Services\PartnerRemittanceService;
use App\Centresidence\Services\TokenEngine;
use App\Centresidence\Support\Money;
use App\Models\Property;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Production-hardening guards: token idempotency, the topology 100%-allocation
 * invariant, partial-month proration, and the partner payout adapter.
 */
class HardeningTest extends CentresidenceDatabaseTestCase
{
    private function waterPmWithTokenConfig(): PropertyModule
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true, 'token_unit_label' => 'Litres']);
        $pm = PropertyModule::create([
            'property_id' => 1, 'module_id' => $module->id, 'owner_id' => 1, 'active_meter_count' => 20,
            'billing_model' => PropertyModule::BILLING_SUBSCRIPTION, 'status' => PropertyModule::STATUS_ACTIVE,
        ]);
        $pm->tokenConfig()->create(['token_unit_label' => 'Litres', 'units_per_kes' => '5', 'centresidence_commission_per_token_unit' => '0.02']);

        return $pm->fresh('tokenConfig');
    }

    // ── Token idempotency ─────────────────────────────────────────────────

    public function test_token_purchase_is_idempotent_on_payment_reference(): void
    {
        $pm = $this->waterPmWithTokenConfig();
        DB::table('users')->insert(['id' => 3, 'first_name' => 'T', 'last_name' => 'N']);
        $engine = app(TokenEngine::class);

        $first = $engine->purchase($pm, 3, Money::fromDecimal('100.00'), ['payment_reference' => 'MPESA-TXN-1']);
        $second = $engine->purchase($pm, 3, Money::fromDecimal('100.00'), ['payment_reference' => 'MPESA-TXN-1']);

        $this->assertSame($first->id, $second->id);          // same purchase, not a new one
        $this->assertSame(1, TokenPurchase::count());         // not double-recorded
        $this->assertSame('500.0000', UtilityWallet::first()->balance_units); // credited once
    }

    // ── Topology 100%-allocation invariant ────────────────────────────────

    public function test_allocation_invariant(): void
    {
        $gw = Gateway::create(['name' => 'GW', 'is_simulated' => true]);
        InfrastructureTopology::create([
            'infrastructure_type' => InfrastructureTopology::TYPE_GATEWAY, 'infrastructure_id' => $gw->id,
            'owner_id' => 1, 'property_id' => 1, 'allocation_percentage' => 60, 'monthly_base_cost' => 10000,
            'effective_from' => '2026-06-01',
        ]);

        $this->assertEqualsWithDelta(60.0, InfrastructureTopology::totalAllocationFor('gateway', $gw->id, '2026-06-07'), 0.001);
        $this->assertFalse(InfrastructureTopology::wouldExceed100('gateway', $gw->id, '2026-06-07', 40));
        $this->assertTrue(InfrastructureTopology::wouldExceed100('gateway', $gw->id, '2026-06-07', 50));

        InfrastructureTopology::assertValidAllocation('gateway', $gw->id, '2026-06-07', 40); // ok
        $this->expectException(AllocationExceededException::class);
        InfrastructureTopology::assertValidAllocation('gateway', $gw->id, '2026-06-07', 50); // 60+50 > 100
    }

    // ── Partial-month proration ───────────────────────────────────────────

    public function test_partial_month_proration(): void
    {
        $month = Carbon::parse('2026-06-01'); // 30-day month
        $gw = Gateway::create(['name' => 'GW', 'is_simulated' => true]);
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true]);
        $module->costComponents()->createMany([
            ['component_name' => 'platform_software_fee', 'cost_model' => ModuleCostComponent::COST_MODEL_PER_ACTIVE_DEVICE, 'rate' => 50, 'requires_gateway' => false, 'is_fallback_eligible' => true, 'is_prorated' => true],
            ['component_name' => 'lorawan_gateway_usage', 'cost_model' => ModuleCostComponent::COST_MODEL_PER_ACTIVE_DEVICE, 'rate' => 50, 'requires_gateway' => true, 'is_fallback_eligible' => true, 'is_prorated' => true],
        ]);
        // Module activated on the 16th → 15 of 30 days → half billing.
        PropertyModule::create([
            'property_id' => 1, 'module_id' => $module->id, 'owner_id' => 1, 'active_meter_count' => 20,
            'billing_model' => PropertyModule::BILLING_SUBSCRIPTION, 'status' => PropertyModule::STATUS_ACTIVE,
            'activated_at' => Carbon::parse('2026-06-16'),
        ]);
        InfrastructureTopology::create([
            'infrastructure_type' => InfrastructureTopology::TYPE_GATEWAY, 'infrastructure_id' => $gw->id,
            'owner_id' => 1, 'property_id' => 1, 'allocation_percentage' => 100, 'monthly_base_cost' => 5000,
            'effective_from' => '2026-06-01',
        ]);

        $invoice = app(CommissionEngine::class)->generateForProperty(Property::find(1), $month, Money::zero());

        // 20 meters × (50 + 50) × 15/30 = 1,000 (half of the full 2,000).
        $this->assertSame('1000.00', $invoice->metered_commission_total);
    }

    // ── Partner payout adapter ────────────────────────────────────────────

    public function test_payout_log_driver_marks_sent(): void
    {
        config(['centresidence.payouts.driver' => 'log']);
        $partner = FinancePartner::create(['company_name' => 'Acme', 'status' => FinancePartner::STATUS_ACTIVE,
            'settlement_account_details' => ['type' => 'mpesa_b2c', 'phone' => '254700000000']]);
        $batch = PartnerRemittanceBatch::create(['finance_partner_id' => $partner->id, 'total_amount' => 20000, 'status' => PartnerRemittanceBatch::STATUS_PREPARED]);

        app(PartnerRemittanceService::class)->payBatch($batch);

        $batch->refresh();
        $this->assertSame(PartnerRemittanceBatch::STATUS_SENT, $batch->status);
        $this->assertStringStartsWith('LOG-', $batch->reference);
    }

    public function test_payout_mpesa_unconfigured_marks_failed(): void
    {
        config(['centresidence.payouts.driver' => 'mpesa', 'mpesa.mpesa_consumer_key' => null]);
        $partner = FinancePartner::create(['company_name' => 'Acme', 'status' => FinancePartner::STATUS_ACTIVE,
            'settlement_account_details' => ['type' => 'mpesa_b2c', 'phone' => '254700000000']]);
        $batch = PartnerRemittanceBatch::create(['finance_partner_id' => $partner->id, 'total_amount' => 20000, 'status' => PartnerRemittanceBatch::STATUS_PREPARED]);

        app(PartnerRemittanceService::class)->payBatch($batch);

        $this->assertSame(PartnerRemittanceBatch::STATUS_FAILED, $batch->fresh()->status);
    }
}
