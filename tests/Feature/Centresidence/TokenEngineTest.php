<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Models\UtilityWallet;
use App\Centresidence\Services\CommissionFallbackService;
use App\Centresidence\Services\TokenEngine;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * WP4 — Token Engine + embedded commission + dual-pathway fallback. Validated
 * against handbook §19 Test Cases 3 (fallback) & 4 (token economics) — the
 * keystone that proves tenant-continuity.
 */
class TokenEngineTest extends CentresidenceDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // A tenant (renter) user for wallets/purchases.
        DB::table('users')->insert(['id' => 3, 'first_name' => 'Test', 'last_name' => 'Tenant']);
    }

    /** Water property-module with token config (KES 1 = N units, commission/unit). */
    private function waterPropertyModule(string $unitsPerKes, string $commissionPerUnit, int $meters = 20): PropertyModule
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true, 'token_unit_label' => 'Litres']);
        $pm = PropertyModule::create([
            'property_id' => 1,
            'module_id' => $module->id,
            'owner_id' => 1,
            'active_meter_count' => $meters,
            'billing_model' => PropertyModule::BILLING_SUBSCRIPTION,
            'status' => PropertyModule::STATUS_ACTIVE,
        ]);
        $pm->tokenConfig()->create([
            'token_unit_label' => 'Litres',
            'units_per_kes' => $unitsPerKes,
            'centresidence_commission_per_token_unit' => $commissionPerUnit,
        ]);

        return $pm->fresh('tokenConfig');
    }

    // ── Handbook §19 Test Case 4: Token Economics Integrity ───────────────

    public function test_case_4_token_economics_integrity(): void
    {
        $pm = $this->waterPropertyModule('5', '0.02');

        $purchase = app(TokenEngine::class)->purchase($pm, 3, Money::fromDecimal('100.00'));

        // Tenant receives 500 litres for KES 100.
        $this->assertSame('500.0000', $purchase->units);
        // Centresidence commission = 500 × 0.02 = KES 10.00 (embedded).
        $this->assertSame('10.00', $purchase->centresidence_commission);
        // Owner revenue = KES 90.00; no fallback active.
        $this->assertSame('90.00', $purchase->owner_revenue_gross);
        $this->assertSame('0.00', $purchase->fallback_deducted);
        $this->assertSame('90.00', $purchase->owner_revenue_net);

        // Wallet credited full units.
        $wallet = UtilityWallet::first();
        $this->assertSame('500.0000', $wallet->balance_units);
    }

    public function test_owner_net_token_revenue_credited_to_wallet(): void
    {
        $pm = $this->waterPropertyModule('5', '0.02');

        app(TokenEngine::class)->purchase($pm, 3, Money::fromDecimal('100.00')); // owner net 90

        // The owner's net token revenue lands in their wallet (uniform for all modes).
        $this->assertSame(90.0, (float) \App\Models\OwnerWallet::forUser(1)->balance);
        $this->assertSame(1, \App\Models\WalletTransaction::where('transaction_source', 'token')->where('net_amount', 90)->count());
    }

    // ── Handbook §19 Test Case 3: Fallback Execution ──────────────────────

    public function test_case_3_fallback_recovers_metered_only_never_locks(): void
    {
        $pm = $this->waterPropertyModule('5', '0.02');

        // Overdue commission invoice: metered KES 2,000 (water) + non-metered
        // KES 375 (locks). billing month = May → overdue by June.
        $invoice = CentresidenceCommissionInvoice::create([
            'owner_id' => 1,
            'property_id' => 1,
            'billing_month' => Carbon::parse('2026-05-01'),
            'subscription_amount' => 10000,
            'metered_commission_total' => 2000,
            'non_metered_commission_total' => 375,
            'total_amount' => 12375,
            'status' => CentresidenceCommissionInvoice::STATUS_PENDING,
        ]);

        // Grace expires → fallback activates (metered only).
        $activated = app(CommissionFallbackService::class)->activateOverdue(Carbon::parse('2026-06-01'));
        $this->assertSame(1, $activated);
        $this->assertTrue($invoice->fresh()->fallback_deduction_active);

        // Tenant buys KES 2,500 of tokens. Owner gross = 2,500 − commission.
        // units = 12,500; commission = 12,500 × 0.02 = 250; owner gross = 2,250.
        $purchase = app(TokenEngine::class)->purchase($pm, 3, Money::fromDecimal('2500.00'));

        // Fallback intercepts the metered 2,000 from owner revenue (cap 100%).
        $this->assertSame('2000.00', $purchase->fallback_deducted);
        $this->assertSame('2250.00', $purchase->owner_revenue_gross);
        $this->assertSame('250.00', $purchase->owner_revenue_net);
        // Commission still flows to Centresidence, separate from the fallback.
        $this->assertSame('250.00', $purchase->centresidence_commission);
        // Tenant got full units regardless.
        $this->assertSame('12500.0000', $purchase->units);

        $invoice->refresh();
        // Metered portion cleared…
        $this->assertSame('2000.00', $invoice->metered_paid_total);
        $this->assertFalse($invoice->fallback_deduction_active);
        $this->assertNotNull($invoice->fallback_metered_cleared_at);
        // …but the locks (non-metered) are UNTOUCHED and remain on the invoice.
        $this->assertSame('375.00', $invoice->non_metered_commission_total);
        $this->assertSame(CentresidenceCommissionInvoice::STATUS_PARTIALLY_PAID, $invoice->status);
    }

    public function test_non_metered_only_invoice_never_activates_fallback(): void
    {
        // Locks-only overdue invoice: nothing metered to recover.
        $invoice = CentresidenceCommissionInvoice::create([
            'owner_id' => 1,
            'property_id' => 1,
            'billing_month' => Carbon::parse('2026-05-01'),
            'metered_commission_total' => 0,
            'non_metered_commission_total' => 375,
            'total_amount' => 375,
            'status' => CentresidenceCommissionInvoice::STATUS_PENDING,
        ]);

        $activated = app(CommissionFallbackService::class)->activateOverdue(Carbon::parse('2026-06-01'));

        $this->assertSame(0, $activated);
        $this->assertFalse($invoice->fresh()->fallback_deduction_active);
    }

    public function test_fallback_accumulates_across_purchases(): void
    {
        $pm = $this->waterPropertyModule('5', '0.02');
        $invoice = CentresidenceCommissionInvoice::create([
            'owner_id' => 1,
            'property_id' => 1,
            'billing_month' => Carbon::parse('2026-05-01'),
            'metered_commission_total' => 2000,
            'non_metered_commission_total' => 0,
            'total_amount' => 2000,
            'status' => CentresidenceCommissionInvoice::STATUS_PENDING,
        ]);
        app(CommissionFallbackService::class)->activateOverdue(Carbon::parse('2026-06-01'));

        $engine = app(TokenEngine::class);

        // Purchase 1: KES 1,000 → owner gross 900 → deduct 900 (partial).
        $p1 = $engine->purchase($pm, 3, Money::fromDecimal('1000.00'));
        $this->assertSame('900.00', $p1->fallback_deducted);
        $this->assertTrue($invoice->fresh()->fallback_deduction_active);
        $this->assertSame('900.00', $invoice->fresh()->metered_paid_total);

        // Purchase 2: KES 1,500 → owner gross 1,350 → deduct remaining 1,100.
        $p2 = $engine->purchase($pm, 3, Money::fromDecimal('1500.00'));
        $this->assertSame('1100.00', $p2->fallback_deducted);

        $invoice->refresh();
        $this->assertSame('2000.00', $invoice->metered_paid_total);
        $this->assertFalse($invoice->fallback_deduction_active);
        // No non-metered remained → fully paid.
        $this->assertSame(CentresidenceCommissionInvoice::STATUS_PAID, $invoice->status);
    }

    public function test_non_positive_purchase_is_rejected(): void
    {
        $pm = $this->waterPropertyModule('5', '0.02');
        $this->expectException(\RuntimeException::class);
        app(TokenEngine::class)->purchase($pm, 3, Money::zero());
    }

    public function test_consumption_draws_down_wallet_balance(): void
    {
        $pm = $this->waterPropertyModule('5', '0.02');
        $engine = app(TokenEngine::class);
        $engine->purchase($pm, 3, Money::fromDecimal('100.00')); // 500 litres

        $wallet = UtilityWallet::first();
        $engine->recordConsumption($wallet, '120.5');

        $wallet->refresh();
        $this->assertSame('379.5000', $wallet->balance_units);
        $this->assertSame('120.5000', $wallet->total_consumed_units);
    }
}
