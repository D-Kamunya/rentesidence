<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\FacilityDefault;
use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\FinanceAnalyticsSnapshot;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\RepaymentSchedule;
use App\Centresidence\Services\FacilityCollectionsService;
use App\Centresidence\Services\FacilityRestructureService;
use App\Centresidence\Services\FinanceAnalyticsService;
use Illuminate\Support\Carbon;

/**
 * WP9 — collections/defaults, restructuring, and finance analytics.
 */
class FacilityCollectionsTest extends CentresidenceDatabaseTestCase
{
    private int $moduleId;
    private int $partnerId;
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
        $this->appId = FinanceApplication::create([
            'owner_id' => 1, 'property_id' => 1, 'module_id' => $module->id,
            'finance_partner_id' => $partner->id, 'finance_partner_module_id' => $pm->id,
            'quantity' => 1, 'requested_amount' => 50000, 'status' => FinanceApplication::STATUS_APPROVED,
        ])->id;
    }

    private function facility(array $attrs = []): FinanceFacility
    {
        return FinanceFacility::create(array_merge([
            'finance_application_id' => $this->appId, 'finance_partner_id' => $this->partnerId,
            'owner_id' => 1, 'property_id' => 1, 'module_id' => $this->moduleId,
            'disbursed_amount' => 50000, 'principal_amount' => 50000,
            'outstanding_principal' => 50000, 'outstanding_interest' => 5000, 'outstanding_penalty' => 0,
            'total_repayable' => 55000, 'monthly_target' => 5000, 'interest_rate' => 18,
            'penalty_rate' => 24, 'repayment_months' => 12, 'grace_period_days' => 5, 'default_threshold_days' => 30,
            'disbursement_date' => Carbon::now()->toDateString(),
            'status' => FinanceFacility::STATUS_ACTIVE,
        ], $attrs));
    }

    private function overdueSchedule(FinanceFacility $facility, string $dueDate): void
    {
        $facility->schedules()->create([
            'period_number' => 1, 'due_date' => $dueDate,
            'opening_balance' => 50000, 'principal_due' => 4250, 'interest_due' => 750, 'total_due' => 5000,
            'closing_balance' => 45750, 'status' => RepaymentSchedule::STATUS_PENDING,
        ]);
    }

    // ── Default detection ─────────────────────────────────────────────────

    public function test_facility_past_threshold_defaults(): void
    {
        $facility = $this->facility();
        // A period due 40 days ago; threshold is 30 days.
        $this->overdueSchedule($facility, Carbon::now()->subDays(40)->toDateString());

        $summary = app(FacilityCollectionsService::class)->run(Carbon::now());

        $this->assertSame(1, $summary['defaulted']);
        $facility->refresh();
        $this->assertSame(FinanceFacility::STATUS_DEFAULTED, $facility->status);
        $this->assertGreaterThan(0, (float) $facility->outstanding_penalty); // penalty accrued
        $this->assertGreaterThanOrEqual(40, $facility->days_past_due);

        // Interest now accrues from the schedule: the one matured period's
        // interest_due (750) is owed, not the artificial 5,000 seed.
        $this->assertSame('750.00', $facility->outstanding_interest);

        $default = FacilityDefault::where('finance_facility_id', $facility->id)->first();
        $this->assertNotNull($default);
        // 50k principal + 750 matured interest + accrued penalty (> 0).
        $this->assertGreaterThan(50750.0, (float) $default->total_outstanding_at_default);
        $this->assertSame('50000.00', $default->outstanding_principal_at_default);
    }

    public function test_within_grace_is_not_overdue_or_defaulted(): void
    {
        $facility = $this->facility();
        // Due 3 days ago, grace 5 days → not overdue.
        $this->overdueSchedule($facility, Carbon::now()->subDays(3)->toDateString());

        $summary = app(FacilityCollectionsService::class)->run(Carbon::now());

        $this->assertSame(0, $summary['overdue']);
        $this->assertSame(0, $summary['defaulted']);
        $this->assertSame(FinanceFacility::STATUS_ACTIVE, $facility->fresh()->status);
    }

    public function test_default_detection_is_idempotent(): void
    {
        $facility = $this->facility();
        $this->overdueSchedule($facility, Carbon::now()->subDays(40)->toDateString());
        $svc = app(FacilityCollectionsService::class);

        $svc->run(Carbon::now());
        // Facility is now defaulted (no longer active) → second run does nothing.
        $svc->run(Carbon::now());

        $this->assertSame(1, FacilityDefault::where('finance_facility_id', $facility->id)->count());
    }

    // ── Restructure ───────────────────────────────────────────────────────

    public function test_restructure_regenerates_schedule_and_resolves_default(): void
    {
        $facility = $this->facility();
        $this->overdueSchedule($facility, Carbon::now()->subDays(40)->toDateString());
        app(FacilityCollectionsService::class)->run(Carbon::now());
        $default = FacilityDefault::where('finance_facility_id', $facility->id)->firstOrFail();

        $restructure = app(FacilityRestructureService::class)->restructure($default, [
            'new_interest_rate' => 12.0, 'new_repayment_months' => 24, 'new_deduction_percentage' => 25.0,
            'restructure_fee' => 1000,
        ]);

        $facility->refresh();
        $this->assertSame(FinanceFacility::STATUS_ACTIVE, $facility->status);
        $this->assertSame(24, $facility->repayment_months);
        $this->assertEqualsWithDelta(12.0, (float) $facility->interest_rate, 0.001);
        // 24 fresh schedule rows; the old overdue row was replaced.
        $this->assertCount(24, $facility->schedules()->get());
        // schedules() is ordered ascending → the last row is the final period.
        $this->assertSame('0.00', $facility->schedules()->get()->last()->closing_balance);

        // Default resolved.
        $this->assertNotNull($default->fresh()->resolved_at);
        $this->assertSame(FacilityDefault::RESOLUTION_RESTRUCTURED, $default->fresh()->resolution_type);
        $this->assertSame($facility->id, $restructure->finance_facility_id);
    }

    // ── Analytics snapshot ────────────────────────────────────────────────

    public function test_analytics_snapshot_aggregates_portfolio(): void
    {
        $this->facility(); // active: principal 50k, interest 5k, monthly 5k, fee 0
        $this->facility(['outstanding_principal' => 30000, 'outstanding_interest' => 2000, 'monthly_target' => 3000, 'platform_fee_amount' => 3850]);
        // A defaulted facility.
        $this->facility(['status' => FinanceFacility::STATUS_DEFAULTED]);

        $snap = app(FinanceAnalyticsService::class)->takeSnapshot(Carbon::now());

        $this->assertSame(2, $snap->total_active_facilities);
        $this->assertSame('80000.00', $snap->total_outstanding_principal); // 50k + 30k
        $this->assertSame('8000.00', $snap->total_expected_monthly); // 5k + 3k
        $this->assertSame(1, $snap->facilities_in_default);
        // default rate = 1 / (2 active + 1 default) = 33.33%
        $this->assertEqualsWithDelta(33.33, (float) $snap->default_rate, 0.01);
        $this->assertSame('3850.00', $snap->total_platform_fees_month);

        // Idempotent per day.
        app(FinanceAnalyticsService::class)->takeSnapshot(Carbon::now());
        $this->assertSame(1, FinanceAnalyticsSnapshot::whereDate('snapshot_date', Carbon::now()->toDateString())->count());
    }
}
