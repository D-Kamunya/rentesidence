<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\OwnerInfrastructureInvoice;
use App\Centresidence\Models\SettlementTransaction;
use App\Centresidence\Services\FinanceApplicationService;
use App\Centresidence\Services\PartnerRemittanceService;
use App\Centresidence\Services\RentSettlementService;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;

/**
 * WP8 — Settlement Engine: the §9.6 rent-deduction priority algorithm, facility
 * repayment from rent, idempotency, and partner remittance batching.
 */
class RentSettlementTest extends CentresidenceDatabaseTestCase
{
    private int $moduleId;
    private int $partnerId;
    private int $appId;

    protected function setUp(): void
    {
        parent::setUp();
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true, 'is_financeable' => true]);
        $this->moduleId = $module->id;
        $partner = FinancePartner::create(['company_name' => 'Acme', 'status' => FinancePartner::STATUS_ACTIVE]);
        $this->partnerId = $partner->id;
        $partnerModule = FinancePartnerModule::create([
            'finance_partner_id' => $partner->id, 'module_id' => $module->id,
            'product_name' => 'Loan', 'interest_rate' => 18, 'min_repayment_months' => 12, 'max_repayment_months' => 36,
        ]);
        // A minimal approved application to satisfy the facility FK.
        $this->appId = FinanceApplication::create([
            'owner_id' => 1, 'property_id' => 1, 'module_id' => $module->id,
            'finance_partner_id' => $partner->id, 'finance_partner_module_id' => $partnerModule->id,
            'quantity' => 1, 'requested_amount' => 50000, 'status' => FinanceApplication::STATUS_APPROVED,
        ])->id;
    }

    private function facility(int $deductionPct, string $outstandingPrincipal, bool $accelerated = true): FinanceFacility
    {
        // Default to accelerated so these priority/cap tests exercise the raw
        // percentage deduction (the per-cycle monthly-target cap is covered
        // separately).
        return FinanceFacility::create([
            'finance_application_id' => $this->appId,
            'finance_partner_id' => $this->partnerId,
            'owner_id' => 1, 'property_id' => 1, 'module_id' => $this->moduleId,
            'disbursed_amount' => $outstandingPrincipal, 'principal_amount' => $outstandingPrincipal,
            'outstanding_principal' => $outstandingPrincipal, 'outstanding_interest' => 0, 'outstanding_penalty' => 0,
            'total_repayable' => $outstandingPrincipal,
            'deduction_percentage' => $deductionPct, 'monthly_target' => 5000, 'repayment_months' => 12,
            'accelerated_repayment' => $accelerated,
            'status' => FinanceFacility::STATUS_ACTIVE,
            'disbursement_status' => FinanceFacility::DISBURSE_DONE, // funded → repayable
        ]);
    }

    private function overdueFallbackInvoice(string $metered): CentresidenceCommissionInvoice
    {
        return CentresidenceCommissionInvoice::create([
            'owner_id' => 1, 'property_id' => 1, 'billing_month' => Carbon::parse('2026-05-01'),
            'metered_commission_total' => $metered, 'metered_paid_total' => 0,
            'non_metered_commission_total' => 0, 'total_amount' => $metered,
            'status' => CentresidenceCommissionInvoice::STATUS_OVERDUE, 'fallback_deduction_active' => true,
        ]);
    }

    private function infraInvoice(string $total): OwnerInfrastructureInvoice
    {
        return OwnerInfrastructureInvoice::create([
            'owner_id' => 1, 'property_id' => 1, 'billing_month' => Carbon::parse('2026-05-01'),
            'total_amount' => $total, 'paid_total' => 0, 'breakdown_json' => [],
            'status' => OwnerInfrastructureInvoice::STATUS_PENDING,
        ]);
    }

    // ── Handbook §9.6 priority + caps ─────────────────────────────────────

    public function test_infrastructure_cost_recovered_from_rent(): void
    {
        $invoice = $this->infraInvoice('3000'); // owed 3,000

        $result = app(RentSettlementService::class)->handleRentPayment(
            1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 9200]
        );

        $this->assertSame('3000.00', $result['infrastructure']);
        $this->assertSame('3000.00', $result['total_deducted']);
        $this->assertSame('97000.00', $result['owner_net']);

        $invoice->refresh();
        $this->assertSame('3000.00', $invoice->paid_total);
        $this->assertSame(OwnerInfrastructureInvoice::STATUS_PAID, $invoice->status);
        $this->assertSame(1, SettlementTransaction::where('transaction_type', 'infrastructure_recovery')->count());
    }

    public function test_owner_consented_cap_raises_the_ceiling(): void
    {
        // Facility wants 80% of rent; default cap (60%) would clip to 60,000,
        // but the owner consented to an 80% personal cap → 80,000 deducted.
        FinanceFacility::create([
            'finance_application_id' => $this->appId, 'finance_partner_id' => $this->partnerId,
            'owner_id' => 1, 'property_id' => 1, 'module_id' => $this->moduleId,
            'disbursed_amount' => '100000', 'principal_amount' => '100000',
            'outstanding_principal' => '100000', 'outstanding_interest' => 0, 'outstanding_penalty' => 0,
            'total_repayable' => '100000', 'deduction_percentage' => 80, 'consented_deduction_cap' => 80,
            'monthly_target' => 5000, 'repayment_months' => 12, 'accelerated_repayment' => true,
            'status' => FinanceFacility::STATUS_ACTIVE, 'disbursement_status' => FinanceFacility::DISBURSE_DONE,
        ]);

        $result = app(RentSettlementService::class)->handleRentPayment(
            1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 9300]
        );

        $this->assertSame('80000.00', $result['total_deducted']);
        $this->assertSame('20000.00', $result['owner_net']);
    }

    public function test_global_cap_limits_total_rent_deduction(): void
    {
        // Fallback wants 50% (50,000) and the facility wants 20% (20,000) → 70%
        // uncapped. The 60% global ceiling clips the total to 60,000, and the
        // facility only gets the remaining budget after fallback.
        $this->overdueFallbackInvoice('50000');
        $facility = $this->facility(20, '50000');

        $result = app(RentSettlementService::class)->handleRentPayment(
            1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 9100]
        );

        $this->assertSame('50000.00', $result['fallback']);
        $this->assertSame('60000.00', $result['total_deducted']);   // clipped from 70,000
        $this->assertSame('40000.00', $result['owner_net']);        // owner keeps ≥ 40%
        $this->assertSame('40000.00', $facility->fresh()->outstanding_principal); // only 10,000 taken
    }

    public function test_undisbursed_facility_is_not_repaid_until_disbursed(): void
    {
        $facility = $this->facility(20, '50000');
        // Roll it back to awaiting — the funds were never actually released.
        $facility->forceFill(['disbursement_status' => FinanceFacility::DISBURSE_AWAITING])->save();

        // Rent arrives, but an undisbursed facility is not an obligation → nothing happens.
        $result = app(RentSettlementService::class)->handleRentPayment(
            1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 7101]
        );
        $this->assertNull($result, 'an undisbursed facility must not be repaid');
        $this->assertSame('50000.00', $facility->fresh()->outstanding_principal);

        // Once the money is actually disbursed, rent repays it (20% of 100k).
        app(\App\Centresidence\Services\FinanceFacilityService::class)->disburse($facility);
        $result = app(RentSettlementService::class)->handleRentPayment(
            1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 7102]
        );
        $this->assertNotNull($result);
        $this->assertSame('30000.00', $facility->fresh()->outstanding_principal);
    }

    public function test_deduction_priority_fallback_then_facilities_then_owner(): void
    {
        $invoice = $this->overdueFallbackInvoice('1500');
        $facilityA = $this->facility(20, '50000'); // oldest
        $facilityB = $this->facility(10, '50000');

        $result = app(RentSettlementService::class)->handleRentPayment(
            1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 9001]
        );

        // Fallback 1,500 + A (20% = 20,000) + B (10% = 10,000) = 31,500 deducted.
        $this->assertSame('1500.00', $result['fallback']);
        $this->assertSame('31500.00', $result['total_deducted']);
        $this->assertSame('68500.00', $result['owner_net']);

        // Facility balances reduced.
        $this->assertSame('30000.00', $facilityA->fresh()->outstanding_principal);
        $this->assertSame('40000.00', $facilityB->fresh()->outstanding_principal);

        // Fallback metered cleared.
        $invoice->refresh();
        $this->assertSame('1500.00', $invoice->metered_paid_total);
        $this->assertFalse($invoice->fallback_deduction_active);

        // Ledger: 1 commission_recovery (Centresidence) + 2 partner deductions.
        $this->assertSame(1, SettlementTransaction::where('transaction_type', 'commission_recovery')->count());
        $partnerTotal = SettlementTransaction::where('beneficiary_type', 'finance_partner')->sum('amount');
        $this->assertEqualsWithDelta(30000.0, (float) $partnerTotal, 0.001);
    }

    public function test_deduction_capped_by_outstanding(): void
    {
        // Facility owes only 3,000 but would request 20,000 — capped at 3,000.
        $facility = $this->facility(20, '3000');

        $result = app(RentSettlementService::class)->handleRentPayment(
            1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 9002]
        );

        $this->assertSame('3000.00', $result['total_deducted']);
        $this->assertSame('0.00', $facility->fresh()->outstanding_principal);
        // Fully repaid → facility completed.
        $this->assertSame(FinanceFacility::STATUS_COMPLETED, $facility->fresh()->status);
    }

    public function test_idempotent_per_rent_transaction(): void
    {
        $this->facility(20, '50000');

        $first = app(RentSettlementService::class)->handleRentPayment(1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 9003]);
        $second = app(RentSettlementService::class)->handleRentPayment(1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 9003]);

        $this->assertNotNull($first);
        $this->assertNull($second); // already processed
        $this->assertSame(1, SettlementTransaction::where('rent_transaction_id', 9003)->count());
    }

    public function test_no_obligations_is_a_noop(): void
    {
        // Property 2 has no facilities or fallback.
        $result = app(RentSettlementService::class)->handleRentPayment(2, 2, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 9004]);
        $this->assertNull($result);
    }

    // ── Partner remittance batching ───────────────────────────────────────

    public function test_remittance_batches_pending_partner_transactions(): void
    {
        $this->facility(20, '50000');
        app(RentSettlementService::class)->handleRentPayment(1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 9005]);

        $batch = app(PartnerRemittanceService::class)->prepareBatchForPartner($this->partnerId);

        $this->assertNotNull($batch);
        $this->assertStringStartsWith('REM-', $batch->batch_number);
        // gross = 20% of 100k = 20,000; this facility carries no origination fee, so
        // only the 1% servicing fee is netted → partner remitted 19,800.
        $this->assertSame('20000.00', $batch->gross_amount);
        $this->assertSame('200.00', $batch->servicing_fee);
        $this->assertSame('0.00', $batch->origination_fee);
        $this->assertSame('19800.00', $batch->net_amount);
        $this->assertSame('19800.00', $batch->total_amount); // payout pays the NET
        $this->assertSame(1, $batch->items()->count());

        // Re-running finds nothing new (transactions now reconciled).
        $this->assertNull(app(PartnerRemittanceService::class)->prepareBatchForPartner($this->partnerId));
    }

    public function test_remittance_nets_servicing_and_origination(): void
    {
        $f = $this->facility(20, '50000');
        // Booked origination owed (as createFromApplication would): 1,000.
        $f->forceFill(['origination_fee_amount' => 1000, 'origination_fee_collected' => 0])->save();
        app(RentSettlementService::class)->handleRentPayment(1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 9006]);

        $batch = app(PartnerRemittanceService::class)->prepareBatchForPartner($this->partnerId);

        // gross 20,000; servicing 1% = 200; origination outstanding 1,000 ≤ cap (25% = 5,000) → collect 1,000.
        $this->assertSame('20000.00', $batch->gross_amount);
        $this->assertSame('200.00', $batch->servicing_fee);
        $this->assertSame('1000.00', $batch->origination_fee);
        $this->assertSame('18800.00', $batch->net_amount);
        $this->assertSame('1000.00', $f->fresh()->origination_fee_collected);
    }

    public function test_origination_collection_is_capped_so_it_never_starves_the_payout(): void
    {
        $f = $this->facility(20, '50000');
        // A large origination (10,000) must NOT be taken all at once from a 20,000 remittance.
        $f->forceFill(['origination_fee_amount' => 10000, 'origination_fee_collected' => 0])->save();
        app(RentSettlementService::class)->handleRentPayment(1, 1, Money::fromDecimal('100000.00'), ['rent_transaction_id' => 9007]);

        $batch = app(PartnerRemittanceService::class)->prepareBatchForPartner($this->partnerId);

        // origination capped at 25% of 20,000 = 5,000 (rest carries to next cycle).
        $this->assertSame('5000.00', $batch->origination_fee);
        $this->assertSame('5000.00', $f->fresh()->origination_fee_collected);
        // partner still receives the majority: 20,000 − 200 servicing − 5,000 = 14,800 (74%).
        $this->assertSame('14800.00', $batch->net_amount);
    }
}
