<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Centresidence\Services\OwnerBillingStandingService;
use Illuminate\Support\Carbon;

/**
 * B2 stage 1 — an owner's infra standing (current / due / overdue), the infra half
 * of the unified account standing that drives the dashboard banner + readonly gate.
 * Only the metered + non-metered module cost counts — never the plan amount.
 */
class OwnerBillingStandingTest extends CentresidenceDatabaseTestCase
{
    private function svc(): OwnerBillingStandingService
    {
        return app(OwnerBillingStandingService::class);
    }

    private function invoice(int $ownerId, array $attrs = []): CentresidenceCommissionInvoice
    {
        return CentresidenceCommissionInvoice::create(array_merge([
            'owner_id' => $ownerId,
            'property_id' => 1,
            'billing_month' => Carbon::parse('2026-08-01'),
            'subscription_amount' => 1500,
            'metered_commission_total' => 300,
            'non_metered_commission_total' => 200,
            'total_amount' => 2000,
            'status' => CentresidenceCommissionInvoice::STATUS_PENDING,
        ], $attrs));
    }

    public function test_no_invoices_is_current(): void
    {
        $this->assertSame('current', $this->svc()->infraStanding(1)['state']);
    }

    public function test_unpaid_within_grace_is_due(): void
    {
        $asOf = Carbon::parse('2026-08-03'); // 2 days after billing, grace 7
        $this->invoice(1, ['billing_month' => Carbon::parse('2026-08-01')]);

        $standing = $this->svc()->infraStanding(1, $asOf);
        $this->assertSame('due', $standing['state']);
        $this->assertSame(500.0, $standing['amount_due']); // 300 + 200 infra, NOT the 1500 plan
    }

    public function test_unpaid_past_grace_is_overdue(): void
    {
        $asOf = Carbon::parse('2026-08-30'); // 29 days after billing, past grace 7
        $this->invoice(1, ['billing_month' => Carbon::parse('2026-08-01')]);

        $this->assertSame('overdue', $this->svc()->infraStanding(1, $asOf)['state']);
    }

    public function test_paid_invoice_is_current(): void
    {
        $this->invoice(1, ['status' => CentresidenceCommissionInvoice::STATUS_PAID]);

        $this->assertSame('current', $this->svc()->infraStanding(1)['state']);
    }

    public function test_plan_only_invoice_with_no_infra_is_current(): void
    {
        // A bill that's all plan, no module infra → nothing infra owed.
        $this->invoice(1, ['metered_commission_total' => 0, 'non_metered_commission_total' => 0]);

        $standing = $this->svc()->infraStanding(1);
        $this->assertSame('current', $standing['state']);
        $this->assertSame(0.0, $standing['amount_due']);
    }

    public function test_amount_due_sums_infra_across_unpaid_invoices(): void
    {
        $asOf = Carbon::parse('2026-08-03');
        $this->invoice(1, ['billing_month' => Carbon::parse('2026-08-01')]); // 500 infra
        // Different month (one invoice per owner/property/month) → both unpaid, summed.
        $this->invoice(1, ['billing_month' => Carbon::parse('2026-07-01'), 'metered_commission_total' => 100, 'non_metered_commission_total' => 50]); // 150

        $this->assertSame(650.0, $this->svc()->infraStanding(1, $asOf)['amount_due']);
    }
}
