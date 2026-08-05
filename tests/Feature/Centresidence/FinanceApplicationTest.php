<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Events\ApplicationApproved;
use App\Centresidence\Events\ApplicationRejected;
use App\Centresidence\Events\ApplicationStatusChanged;
use App\Centresidence\Events\ApplicationSubmitted;
use App\Centresidence\Exceptions\FacilityInfeasibleException;
use App\Centresidence\Exceptions\InvalidApplicationTransitionException;
use App\Centresidence\Exceptions\UnderwritingFailedException;
use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\Module;
use App\Centresidence\Services\FinanceApplicationService;
use Illuminate\Support\Facades\Event;

/**
 * WP6 — Finance Product + Application engines (handbook §9.2–§9.3): facility
 * auto-calculation, configurable underwriting, and the application lifecycle
 * state machine with an immutable audit trail.
 */
class FinanceApplicationTest extends CentresidenceDatabaseTestCase
{
    private int $moduleId;
    private int $catalogueItemId;
    private int $partnerModuleId;
    private int $partnerId;

    protected function setUp(): void
    {
        parent::setUp();

        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true, 'is_financeable' => true]);
        $this->moduleId = $module->id;

        // Pricing + platform fee (10%).
        $this->catalogueItemId = $module->pricingCatalogueItems()->create([
            'item_name' => 'Water Meter Unit', 'unit_price' => 3500.00, 'unit_label' => 'meter',
        ])->id;
        $module->platformFeeConfigs()->create(['fee_percentage' => 10.00, 'is_active' => true]);

        $partner = FinancePartner::create(['company_name' => 'Acme Capital', 'status' => FinancePartner::STATUS_ACTIVE]);
        $this->partnerId = $partner->id;

        $partnerModule = FinancePartnerModule::create([
            'finance_partner_id' => $partner->id,
            'module_id' => $module->id,
            'product_name' => 'Water Meter Infrastructure Loan',
            'interest_rate_type' => 'reducing_balance',
            'interest_rate' => 18.00,
            'min_amount' => 1000, 'max_amount' => 1000000,
            'min_repayment_months' => 12, 'max_repayment_months' => 36,
            'max_rent_deduction_percentage' => 30.00,
        ]);
        $this->partnerModuleId = $partnerModule->id;

        // Underwriting: occupancy >= 70 (hard), cashflow history >= 3 months (soft).
        $partnerModule->underwritingRules()->createMany([
            ['rule_name' => 'min_occupancy', 'rule_type' => 'threshold', 'parameter' => 'occupancy_rate', 'operator' => 'gte', 'value' => '70', 'is_hard_rule' => true, 'error_message' => 'Occupancy below 70%'],
            ['rule_name' => 'cashflow_history', 'rule_type' => 'threshold', 'parameter' => 'cashflow_history_months', 'operator' => 'gte', 'value' => '3', 'is_hard_rule' => false],
        ]);
    }

    private function draftData(): array
    {
        return [
            'owner_id' => 1, 'property_id' => 1, 'module_id' => $this->moduleId,
            'finance_partner_id' => $this->partnerId, 'finance_partner_module_id' => $this->partnerModuleId,
            'catalogue_item_id' => $this->catalogueItemId, 'quantity' => 10,
        ];
    }

    // ── Auto-calculation (handbook §9.3) ──────────────────────────────────

    public function test_draft_auto_calculates_facility_maths(): void
    {
        $app = app(FinanceApplicationService::class)->createDraft($this->draftData());

        // base = 3500 × 10 = 35,000; fee = 10% = 3,500; requested = 38,500.
        $this->assertSame('35000.00', $app->base_cost);
        $this->assertSame('10.00', $app->platform_fee_percentage);
        $this->assertSame('3500.00', $app->platform_fee_amount);
        $this->assertSame('38500.00', $app->requested_amount);

        // Estimated monthly over 12 months @ 18% reducing balance ≈ 3,530.
        $this->assertGreaterThan(3000, (float) $app->estimated_monthly_repayment);
        $this->assertLessThan(4000, (float) $app->estimated_monthly_repayment);

        $this->assertStringStartsWith('FIN-', $app->application_number);
        $this->assertSame(FinanceApplication::STATUS_DRAFT, $app->status);
        $this->assertCount(1, $app->statusHistory()->get()); // null → draft
    }

    public function test_installation_cost_is_folded_into_the_financed_principal(): void
    {
        // Catalogue with a non-zero install fee: base = (3500 + 800) × 10.
        $item = Module::find($this->moduleId)->pricingCatalogueItems()->create([
            'item_name' => 'Water Meter + Install', 'unit_price' => 3500.00,
            'installation_cost' => 800.00, 'unit_label' => 'meter', 'is_active' => true,
        ]);

        $app = app(FinanceApplicationService::class)->createDraft(
            ['catalogue_item_id' => $item->id] + $this->draftData()
        );

        // base = 4300 × 10 = 43,000; fee = 10% = 4,300; requested = 47,300.
        $this->assertSame('43000.00', $app->base_cost);
        $this->assertSame('4300.00', $app->platform_fee_amount);
        $this->assertSame('47300.00', $app->requested_amount);
    }

    public function test_partial_financing_finances_only_the_remainder(): void
    {
        $service = app(FinanceApplicationService::class);

        // Full finance for the monthly baseline.
        $full = $service->createDraft($this->draftData());

        // Same order with a 10,000 down-payment: total 38,500 → finance 28,500.
        $partial = $service->createDraft(['owner_contribution' => 10000] + $this->draftData());

        $this->assertSame('38500.00', $partial->requested_amount);   // total project cost unchanged
        $this->assertSame('10000.00', $partial->owner_contribution);
        $this->assertSame('28500.00', $partial->financed_amount);
        // Smaller principal → smaller monthly than the full-finance case.
        $this->assertLessThan((float) $full->estimated_monthly_repayment, (float) $partial->estimated_monthly_repayment);

        // The facility finances the net amount and records the contribution.
        $facility = app(\App\Centresidence\Services\FinanceFacilityService::class)->createFromApplication($partial);
        $this->assertSame('28500.00', $facility->principal_amount);
        $this->assertSame('10000.00', $facility->owner_contribution);
    }

    public function test_contribution_is_capped_at_the_total_cost(): void
    {
        $app = app(FinanceApplicationService::class)
            ->createDraft(['owner_contribution' => 99999] + $this->draftData());

        $this->assertSame('38500.00', $app->owner_contribution); // capped to total
        $this->assertSame('0.00', $app->financed_amount);
    }

    // ── Feasibility gate (cap vs partner term) ────────────────────────────

    public function test_facility_blocked_when_repayment_exceeds_rent_cap(): void
    {
        // Monthly ≈ 3,530 over 12 months; at 5,000 rent that's ~70% — above the
        // 60% default cap, so the facility could never repay on its term.
        $this->expectException(FacilityInfeasibleException::class);
        app(FinanceApplicationService::class)->createDraft(['property_rent' => 5000] + $this->draftData());
    }

    public function test_facility_feasible_with_sufficient_rent(): void
    {
        $app = app(FinanceApplicationService::class)->createDraft(['property_rent' => 50000] + $this->draftData());
        $this->assertSame(FinanceApplication::STATUS_DRAFT, $app->status); // ~7% of rent
    }

    public function test_feasibility_gate_counts_the_module_infra_cost(): void
    {
        // Module infra: 100/device × 10 devices = 1,000/mo, on top of the facility.
        Module::find($this->moduleId)->costComponents()->create([
            'component_name' => 'platform_software_fee', 'cost_model' => 'per_active_device',
            'rate' => 100, 'status' => 'active',
        ]);

        // Facility monthly alone (~3,530) is ~50% of 7,000 rent — would fit. With
        // the 1,000 infra it's ~65%, above the 60% cap → blocked.
        $this->expectException(FacilityInfeasibleException::class);
        app(FinanceApplicationService::class)->createDraft(['property_rent' => 7000] + $this->draftData());
    }

    public function test_feasibility_gate_counts_existing_module_infra(): void
    {
        // Facility (~3,530/mo) alone is ~50% of 7,000 rent — would fit. But the
        // owner already runs modules costing 1,500/mo on this property; together
        // (~72%) they exceed the 60% cap → blocked.
        $this->expectException(FacilityInfeasibleException::class);
        app(FinanceApplicationService::class)->createDraft(
            ['property_rent' => 7000, 'existing_infra' => 1500] + $this->draftData()
        );
    }

    public function test_consent_relieves_an_existing_infra_block(): void
    {
        // Same ~72% case — consenting to a 75% cap makes it feasible (the owner's
        // separate meter income makes the higher rent-deduction sustainable).
        $application = app(FinanceApplicationService::class)->createDraft(
            ['property_rent' => 7000, 'existing_infra' => 1500, 'consented_deduction_cap' => 75] + $this->draftData()
        );
        $this->assertEquals(75, $application->consented_deduction_cap);
    }

    public function test_consent_makes_a_tight_facility_feasible(): void
    {
        // ~70% of 5,000 rent: blocked at 60%, allowed once the owner consents to 75%.
        $app = app(FinanceApplicationService::class)->createDraft(
            ['property_rent' => 5000, 'consented_deduction_cap' => 75] + $this->draftData()
        );
        $this->assertSame(75, (int) $app->consented_deduction_cap);
    }

    // ── Underwriting (handbook §9.2.3 / §9.7) ─────────────────────────────

    public function test_submit_passes_soft_underwriting(): void
    {
        Event::fake([ApplicationSubmitted::class, ApplicationStatusChanged::class]);
        $service = app(FinanceApplicationService::class);
        $app = $service->createDraft($this->draftData());

        $service->submit($app, ['occupancy_rate' => 85, 'cashflow_history_months' => 6]);

        $this->assertSame(FinanceApplication::STATUS_SUBMITTED, $app->fresh()->status);
        $this->assertNotNull($app->fresh()->submitted_at);
        $this->assertTrue($app->fresh()->underwriting_result_json['passed']);
        Event::assertDispatched(ApplicationSubmitted::class);
        $this->assertCount(2, $app->statusHistory()->get()); // draft, submitted
    }

    public function test_hard_underwriting_failure_blocks_submission(): void
    {
        $service = app(FinanceApplicationService::class);
        $app = $service->createDraft($this->draftData());

        try {
            $service->submit($app, ['occupancy_rate' => 50, 'cashflow_history_months' => 6]);
            $this->fail('Expected UnderwritingFailedException');
        } catch (UnderwritingFailedException $e) {
            $this->assertNotEmpty($e->hardFailures);
        }

        // Stays a draft; result snapshotted for the owner to see why.
        $this->assertSame(FinanceApplication::STATUS_DRAFT, $app->fresh()->status);
        $this->assertFalse($app->fresh()->underwriting_result_json['passed']);
    }

    public function test_soft_warning_alone_does_not_block(): void
    {
        $service = app(FinanceApplicationService::class);
        $app = $service->createDraft($this->draftData());

        // Occupancy OK (hard passes), but only 1 month history (soft fails).
        $service->submit($app, ['occupancy_rate' => 85, 'cashflow_history_months' => 1]);

        $this->assertSame(FinanceApplication::STATUS_SUBMITTED, $app->fresh()->status);
        $this->assertNotEmpty($app->fresh()->underwriting_result_json['soft_warnings']);
    }

    // ── Lifecycle state machine (handbook §9.3.1) ─────────────────────────

    public function test_full_approval_lifecycle(): void
    {
        Event::fake([ApplicationApproved::class, ApplicationStatusChanged::class, ApplicationSubmitted::class]);
        $service = app(FinanceApplicationService::class);
        $app = $service->createDraft($this->draftData());

        $service->submit($app, ['occupancy_rate' => 85, 'cashflow_history_months' => 6]);
        $service->moveToReview($app);
        $service->approve($app, '38500.00');

        $app->refresh();
        $this->assertSame(FinanceApplication::STATUS_APPROVED, $app->status);
        $this->assertSame('38500.00', $app->approved_amount);
        $this->assertNotNull($app->approved_at);
        Event::assertDispatched(ApplicationApproved::class);

        // Full audit trail: draft, submitted, under_review, approved.
        $this->assertCount(4, $app->statusHistory()->get());
    }

    public function test_rejection_records_reason_and_event(): void
    {
        Event::fake([ApplicationRejected::class, ApplicationStatusChanged::class, ApplicationSubmitted::class]);
        $service = app(FinanceApplicationService::class);
        $app = $service->createDraft($this->draftData());
        $service->submit($app, ['occupancy_rate' => 85, 'cashflow_history_months' => 6]);

        $service->reject($app, 'Insufficient cashflow coverage');

        $app->refresh();
        $this->assertSame(FinanceApplication::STATUS_REJECTED, $app->status);
        $this->assertSame('Insufficient cashflow coverage', $app->rejection_reason);
        Event::assertDispatched(ApplicationRejected::class);
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $service = app(FinanceApplicationService::class);
        $app = $service->createDraft($this->draftData());

        // draft → approved is not allowed (must go through submitted).
        $this->expectException(InvalidApplicationTransitionException::class);
        $service->transitionTo($app, FinanceApplication::STATUS_APPROVED, null);
    }
}
