<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\Module;
use App\Centresidence\Services\PartnerRemittanceService;
use App\Centresidence\Services\RentSettlementService;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;

/**
 * Repayment pacing (per-cycle target cap + accelerated opt-in) and partner
 * settlement cadence.
 */
class RepaymentPacingTest extends CentresidenceDatabaseTestCase
{
    private int $moduleId;
    private int $partnerId;
    private int $partnerModuleId;
    private int $appId;

    protected function setUp(): void
    {
        parent::setUp();
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_financeable' => true]);
        $this->moduleId = $module->id;
        $partner = FinancePartner::create(['company_name' => 'Acme', 'status' => FinancePartner::STATUS_ACTIVE]);
        $this->partnerId = $partner->id;
        $pm = FinancePartnerModule::create([
            'finance_partner_id' => $partner->id, 'module_id' => $module->id, 'product_name' => 'Loan',
            'interest_rate' => 18, 'min_repayment_months' => 12,
        ]);
        $this->partnerModuleId = $pm->id;
        $this->appId = FinanceApplication::create([
            'owner_id' => 1, 'property_id' => 1, 'module_id' => $module->id,
            'finance_partner_id' => $partner->id, 'finance_partner_module_id' => $pm->id,
            'quantity' => 1, 'requested_amount' => 50000, 'status' => FinanceApplication::STATUS_APPROVED,
        ])->id;
    }

    private function facility(bool $accelerated): FinanceFacility
    {
        return FinanceFacility::create([
            'finance_application_id' => $this->appId, 'finance_partner_id' => $this->partnerId,
            'owner_id' => 1, 'property_id' => 1, 'module_id' => $this->moduleId,
            'disbursed_amount' => 50000, 'principal_amount' => 50000,
            'outstanding_principal' => 50000, 'outstanding_interest' => 0, 'outstanding_penalty' => 0,
            'total_repayable' => 50000, 'deduction_percentage' => 20, 'monthly_target' => 5000,
            'repayment_months' => 12, 'accelerated_repayment' => $accelerated,
            'status' => FinanceFacility::STATUS_ACTIVE, 'disbursement_status' => FinanceFacility::DISBURSE_DONE,
        ]);
    }

    public function test_default_pauses_collection_at_monthly_target(): void
    {
        $facility = $this->facility(false); // not accelerated
        $service = app(RentSettlementService::class);

        // Rent 100k × 20% = 20k intended, but capped at the 5k monthly target.
        $first = $service->handleRentPayment(1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 7001]);
        $this->assertSame('5000.00', $first['total_deducted']);

        // Second rent the same month: target already met → nothing deducted.
        $second = $service->handleRentPayment(1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 7002]);
        $this->assertNull($second);

        $this->assertSame('45000.00', $facility->fresh()->outstanding_principal); // only 5k taken
    }

    public function test_accelerated_repayment_bypasses_the_cap(): void
    {
        $facility = $this->facility(true); // accelerated
        $service = app(RentSettlementService::class);

        // Full 20% of 100k deducted, ignoring the 5k monthly target.
        $result = $service->handleRentPayment(1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 7003]);
        $this->assertSame('20000.00', $result['total_deducted']);
        $this->assertSame('30000.00', $facility->fresh()->outstanding_principal);
    }

    // ── Settlement cadence ────────────────────────────────────────────────

    public function test_monthly_partner_due_only_on_settlement_day(): void
    {
        FinancePartnerModule::where('id', $this->partnerModuleId)->update([
            'daily_settlement_enabled' => false, 'monthly_settlement_enabled' => true, 'settlement_day' => 15,
        ]);
        $partner = FinancePartner::find($this->partnerId);
        $service = app(PartnerRemittanceService::class);

        $this->assertTrue($service->isDueToday($partner, Carbon::parse('2026-06-15')));
        $this->assertFalse($service->isDueToday($partner, Carbon::parse('2026-06-10')));
    }

    public function test_daily_partner_is_due_every_day(): void
    {
        FinancePartnerModule::where('id', $this->partnerModuleId)->update(['daily_settlement_enabled' => true]);
        $partner = FinancePartner::find($this->partnerId);
        $service = app(PartnerRemittanceService::class);

        $this->assertTrue($service->isDueToday($partner, Carbon::parse('2026-06-10')));
        $this->assertTrue($service->isDueToday($partner, Carbon::parse('2026-06-23')));
    }
}
