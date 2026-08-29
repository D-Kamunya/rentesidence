<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\FacilityTransaction;
use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\Module;
use App\Centresidence\Services\FacilityCollectionsService;
use App\Centresidence\Services\FacilityInterestService;
use App\Centresidence\Services\FinanceFacilityService;
use Illuminate\Support\Carbon;

/**
 * Interest treatment by type: reducing-balance accrues (early repayment saves
 * interest); flat pre-books it (no saving). Plus early-settlement payoff.
 */
class FacilityInterestTest extends CentresidenceDatabaseTestCase
{
    private function facility(string $interestType): FinanceFacility
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_financeable' => true]);
        $partner = FinancePartner::create(['company_name' => 'Acme', 'status' => FinancePartner::STATUS_ACTIVE]);
        $pm = FinancePartnerModule::create([
            'finance_partner_id' => $partner->id, 'module_id' => $module->id, 'product_name' => 'Loan',
            'interest_rate_type' => $interestType, 'interest_rate' => 18, 'interest_calculation_method' => 'monthly_rest',
            'min_repayment_months' => 12, 'max_repayment_months' => 36,
            'early_repayment_allowed' => true, 'early_repayment_penalty_percentage' => 2.00,
        ]);
        $app = FinanceApplication::create([
            'owner_id' => 1, 'property_id' => 1, 'module_id' => $module->id,
            'finance_partner_id' => $partner->id, 'finance_partner_module_id' => $pm->id,
            'quantity' => 10, 'base_cost' => 35000, 'platform_fee_amount' => 3500, 'requested_amount' => 38500,
            'interest_rate_snapshot' => 18, 'repayment_months' => 12, 'status' => FinanceApplication::STATUS_APPROVED,
        ]);

        return app(FinanceFacilityService::class)->createFromApplication($app);
    }

    // ── Reducing balance: accrual + early-repayment saving ────────────────

    public function test_reducing_balance_starts_with_zero_interest_and_accrues(): void
    {
        $facility = $this->facility('reducing_balance');

        // Nothing matured yet → no interest owed.
        $this->assertSame('0.00', $facility->outstanding_interest);

        // After period 1 matures (~30 days out), interest accrues.
        app(FacilityCollectionsService::class)->run(Carbon::now()->addDays(45));

        $facility->refresh();
        // Period 1 interest = 38,500 × 1.5% = 577.50.
        $this->assertSame('577.50', $facility->outstanding_interest);
    }

    public function test_reducing_balance_early_settlement_saves_future_interest(): void
    {
        $facility = $this->facility('reducing_balance');

        $result = app(FinanceFacilityService::class)->settleEarly($facility);

        // Fresh facility: no interest accrued → payoff is principal + 2% fee only.
        $this->assertSame('38500.00', $result['principal']);
        $this->assertSame('0.00', $result['interest']); // all future interest saved
        $this->assertSame('770.00', $result['fee']);     // 2% of 38,500
        $this->assertSame('39270.00', $result['total']);

        $facility->refresh();
        $this->assertSame(FinanceFacility::STATUS_COMPLETED, $facility->status);
        $this->assertSame('0.00', $facility->outstanding_principal);
        $this->assertSame(1, FacilityTransaction::where('finance_facility_id', $facility->id)
            ->where('transaction_type', 'fee')->count());
    }

    public function test_manual_early_settlement_stays_pending_until_partner_confirms(): void
    {
        $facility = $this->facility('reducing_balance');
        $svc = app(FinanceFacilityService::class);

        // Manual channel → recorded, but NOT completed (no free status flip).
        $res = $svc->initiateEarlySettlement($facility, 'manual', 'BANK-REF-1');
        $this->assertSame('pending', $res['status']);
        $facility->refresh();
        $this->assertSame(FinanceFacility::EARLY_PENDING, $facility->early_settlement_status);
        $this->assertSame(FinanceFacility::STATUS_ACTIVE, $facility->status, 'not completed until confirmed');

        // Partner confirms receipt → completed + the partner is owed the payoff for remittance.
        $svc->confirmEarlySettlement($facility);
        $facility->refresh();
        $this->assertSame(FinanceFacility::STATUS_COMPLETED, $facility->status);
        $this->assertSame(FinanceFacility::EARLY_SETTLED, $facility->early_settlement_status);
        $partnerOwed = \App\Centresidence\Models\SettlementTransaction::where('finance_facility_id', $facility->id)
            ->where('beneficiary_type', 'finance_partner')->sum('amount');
        $this->assertEqualsWithDelta(39270.0, (float) $partnerOwed, 0.01); // principal 38,500 + 2% fee 770
    }

    // ── Flat: interest pre-booked, no early saving ────────────────────────

    public function test_flat_prebooks_interest_and_early_settlement_keeps_it(): void
    {
        $facility = $this->facility('flat');

        // Flat: total interest 38,500 × 18% × 1yr = 6,930 booked at creation.
        $this->assertSame('6930.00', $facility->outstanding_interest);

        $result = app(FinanceFacilityService::class)->settleEarly($facility);

        // No saving: full interest still owed on early settlement.
        $this->assertSame('38500.00', $result['principal']);
        $this->assertSame('6930.00', $result['interest']);
        $this->assertSame('770.00', $result['fee']);
        $this->assertSame('46200.00', $result['total']); // 38,500 + 6,930 + 770
    }

    public function test_early_settlement_blocked_when_partner_disallows(): void
    {
        $facility = $this->facility('reducing_balance');
        // Disallow early repayment on the product.
        $facility->application->partnerModule->update(['early_repayment_allowed' => false]);

        $this->expectException(\RuntimeException::class);
        app(FinanceFacilityService::class)->settleEarly($facility->fresh());
    }
}
