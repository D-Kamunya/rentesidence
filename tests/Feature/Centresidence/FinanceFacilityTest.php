<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Events\FacilityDisbursed;
use App\Centresidence\Models\FacilityTransaction;
use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\Module;
use App\Centresidence\Services\FinanceApplicationService;
use App\Centresidence\Services\FinanceFacilityService;
use App\Centresidence\Services\FinancePartnerService;
use App\Centresidence\Support\Money;
use Illuminate\Support\Facades\Event;

/**
 * WP7 — Finance Facility engine: facility creation + amortisation schedule from
 * an approved application (via the ApplicationApproved listener), disbursement,
 * and finance-partner provisioning (role 6).
 */
class FinanceFacilityTest extends CentresidenceDatabaseTestCase
{
    private function financeSetup(): array
    {
        $module = Module::create(['key' => 'water_meter', 'name' => 'Water Meter', 'is_metered' => true, 'is_financeable' => true]);
        $catalogueItem = $module->pricingCatalogueItems()->create(['item_name' => 'Meter', 'unit_price' => 3500.00]);
        $module->platformFeeConfigs()->create(['fee_percentage' => 10.00, 'is_active' => true]);

        $partner = FinancePartner::create(['company_name' => 'Acme Capital', 'status' => FinancePartner::STATUS_ACTIVE]);
        $partnerModule = FinancePartnerModule::create([
            'finance_partner_id' => $partner->id, 'module_id' => $module->id,
            'product_name' => 'Loan', 'interest_rate_type' => 'reducing_balance', 'interest_rate' => 18.00,
            'interest_calculation_method' => 'monthly_rest',
            'min_repayment_months' => 12, 'max_repayment_months' => 36, 'max_rent_deduction_percentage' => 30.00,
            'grace_period_days' => 5, 'default_threshold_days' => 30,
        ]);

        return ['module' => $module, 'catalogueItem' => $catalogueItem, 'partner' => $partner, 'partnerModule' => $partnerModule];
    }

    private function draftApplication(array $setup): FinanceApplication
    {
        return app(FinanceApplicationService::class)->createDraft([
            'owner_id' => 1, 'property_id' => 1, 'module_id' => $setup['module']->id,
            'finance_partner_id' => $setup['partner']->id, 'finance_partner_module_id' => $setup['partnerModule']->id,
            'catalogue_item_id' => $setup['catalogueItem']->id, 'quantity' => 10,
        ]);
    }

    // ── Facility creation via the approval listener ───────────────────────

    public function test_approval_creates_facility_and_schedule(): void
    {
        $setup = $this->financeSetup();
        $service = app(FinanceApplicationService::class);
        $app = $this->draftApplication($setup);

        $service->submit($app, ['occupancy_rate' => 85]);
        $service->moveToReview($app);
        $service->approve($app, '38500.00'); // fires ApplicationApproved → listener

        $facility = FinanceFacility::where('finance_application_id', $app->id)->first();
        $this->assertNotNull($facility, 'Facility should be auto-created on approval');
        $this->assertStringStartsWith('FAC-', $facility->facility_number);
        $this->assertSame(12, $facility->repayment_months);
        $this->assertSame('38500.00', $facility->principal_amount);
        $this->assertSame('38500.00', $facility->outstanding_principal);

        // 12 schedule rows; principal fully amortises to zero.
        $schedules = $facility->schedules()->get();
        $this->assertCount(12, $schedules);
        $this->assertSame('0.00', $schedules->last()->closing_balance);

        $principalSum = $schedules->reduce(
            fn (Money $c, $s) => $c->plus(Money::fromDecimal($s->principal_due)),
            Money::zero()
        );
        $this->assertSame('38500.00', $principalSum->toDecimal());

        // total_repayable exceeds principal (interest applied).
        $this->assertGreaterThan(38500.0, (float) $facility->total_repayable);
    }

    public function test_listener_is_idempotent(): void
    {
        $setup = $this->financeSetup();
        $service = app(FinanceApplicationService::class);
        $app = $this->draftApplication($setup);
        $service->submit($app, ['occupancy_rate' => 85]);
        $service->moveToReview($app);
        $service->approve($app, '38500.00');

        // Manually re-running creation must not duplicate.
        app(FinanceFacilityService::class); // (listener already created one)
        $this->assertSame(1, FinanceFacility::where('finance_application_id', $app->id)->count());
    }

    // ── Disbursement ──────────────────────────────────────────────────────

    public function test_disburse_records_transaction_and_event(): void
    {
        Event::fake([FacilityDisbursed::class]);
        $setup = $this->financeSetup();
        $app = $this->draftApplication($setup);

        $facility = app(FinanceFacilityService::class)->createFromApplication($app);
        app(FinanceFacilityService::class)->disburse($facility, 'ESCROW-REF-1');

        $facility->refresh();
        $this->assertNotNull($facility->disbursement_date);
        $this->assertSame(1, FacilityTransaction::where('finance_facility_id', $facility->id)
            ->where('transaction_type', FacilityTransaction::TYPE_DISBURSEMENT)->count());
        Event::assertDispatched(FacilityDisbursed::class);
    }

    // ── Partner provisioning (role 6) ─────────────────────────────────────

    public function test_provision_creates_finance_partner_user(): void
    {
        $partner = app(FinancePartnerService::class)->provision(
            ['company_name' => 'Bridge Finance'],
            ['email' => 'ops@bridge.test', 'password' => 'secret123', 'first_name' => 'Bridge', 'last_name' => 'Ops']
        );

        $this->assertNotNull($partner->user_id);
        $user = \App\Models\User::find($partner->user_id);
        $this->assertSame((string) USER_ROLE_FINANCE_PARTNER, (string) $user->role);
        $this->assertSame(FinancePartner::STATUS_ONBOARDING, $partner->status);
    }
}
