<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Centresidence\Services\CommissionFallbackService;
use App\Centresidence\Services\InfraBillPaymentService;
use App\Centresidence\Services\OwnerBillingStandingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Completion-map C1 reconciliation — the interaction between the metered token
 * fallback (recovers metered infra from token revenue) and the owner's infra
 * bill (the merge / standalone charge).
 *
 * Two invariants:
 *  1. No double charge — metered infra already recovered by the fallback is
 *     netted out of what the owner is billed.
 *  2. Cadence (option A) — an ACTIVE MONTHLY owner's token revenue is not
 *     intercepted for infra that isn't due until their plan renewal.
 */
class TokenMergeReconciliationTest extends CentresidenceDatabaseTestCase
{
    private function overdueInvoice(int $ownerId, string $metered, string $meteredPaid, string $nonMetered, string $status): CentresidenceCommissionInvoice
    {
        return CentresidenceCommissionInvoice::create([
            'owner_id'                     => $ownerId,
            'property_id'                  => 1,
            'billing_month'                => Carbon::parse('2026-05-01'),
            'subscription_amount'          => 0,
            'metered_commission_total'     => $metered,
            'metered_paid_total'           => $meteredPaid,
            'non_metered_commission_total' => $nonMetered,
            'total_amount'                 => bcadd($metered, $nonMetered, 2),
            'status'                       => $status,
        ]);
    }

    // ── Invariant 1: no double charge ─────────────────────────────────────

    public function test_owner_bill_nets_out_metered_already_recovered_by_fallback(): void
    {
        // Metered 2,000 with 900 already recovered from token revenue; locks 375.
        $this->overdueInvoice(1, '2000', '900', '375', CentresidenceCommissionInvoice::STATUS_PARTIALLY_PAID);

        $out = app(InfraBillPaymentService::class)->outstanding(1);

        // Owner owes the un-recovered metered (1,100) + non-metered (375) = 1,475,
        // NOT the full 2,375 (that would double-charge the recovered 900).
        $this->assertSame(1475.0, $out['total']);
    }

    public function test_fully_recovered_metered_with_no_locks_leaves_nothing_to_bill(): void
    {
        // Metered fully recovered by fallback, no locks → invoice already paid.
        $this->overdueInvoice(1, '2000', '2000', '0', CentresidenceCommissionInvoice::STATUS_PAID);

        $this->assertSame(0.0, app(InfraBillPaymentService::class)->outstanding(1)['total']);
    }

    public function test_markpaid_settles_the_remainder_and_clears_fallback(): void
    {
        $inv = $this->overdueInvoice(1, '2000', '900', '375', CentresidenceCommissionInvoice::STATUS_PARTIALLY_PAID);
        $inv->forceFill(['fallback_deduction_active' => true])->save();

        app(InfraBillPaymentService::class)->markPaid(1, 'MPESA-1');

        $inv->refresh();
        $this->assertSame(CentresidenceCommissionInvoice::STATUS_PAID, $inv->status);
        $this->assertSame('2000.00', $inv->metered_paid_total); // remainder settled by the owner
        $this->assertFalse($inv->fallback_deduction_active);
        $this->assertSame(0.0, app(InfraBillPaymentService::class)->outstanding(1)['total']);
    }

    // ── Invariant 2: cadence-aware fallback (option A) ────────────────────

    private function setCadence(int $ownerId, int $durationType, ?string $endDate): void
    {
        $orderId = DB::table('subscription_orders')->insertGetId(['duration_type' => $durationType]);
        DB::table('owner_packages')->insert([
            'user_id'       => $ownerId,
            'order_id'      => $orderId,
            'pricing_model' => 'subscription',
            'end_date'      => $endDate,
            'status'        => 1,
        ]);
    }

    public function test_active_monthly_owner_infra_is_not_enforceable(): void
    {
        $this->setCadence(1, PACKAGE_DURATION_TYPE_MONTHLY, Carbon::now()->addDays(10)->toDateString());

        $this->assertFalse(app(OwnerBillingStandingService::class)->mayEnforceInfra(1));
    }

    public function test_expired_monthly_and_yearly_owners_are_enforceable(): void
    {
        $this->setCadence(1, PACKAGE_DURATION_TYPE_MONTHLY, Carbon::now()->subDay()->toDateString());
        $this->setCadence(2, PACKAGE_DURATION_TYPE_YEARLY, Carbon::now()->addMonths(6)->toDateString());

        $standing = app(OwnerBillingStandingService::class);
        $this->assertTrue($standing->mayEnforceInfra(1)); // monthly, lapsed
        $this->assertTrue($standing->mayEnforceInfra(2)); // yearly, still active
    }

    public function test_fallback_skips_active_monthly_owner_but_arms_expired_one(): void
    {
        // Active monthly owner (1) + expired monthly owner (2), each with an
        // overdue metered invoice.
        $this->setCadence(1, PACKAGE_DURATION_TYPE_MONTHLY, Carbon::now()->addDays(10)->toDateString());
        $this->setCadence(2, PACKAGE_DURATION_TYPE_MONTHLY, Carbon::now()->subDay()->toDateString());

        $active  = $this->overdueInvoice(1, '2000', '0', '0', CentresidenceCommissionInvoice::STATUS_PENDING);
        $lapsed  = $this->overdueInvoice(2, '2000', '0', '0', CentresidenceCommissionInvoice::STATUS_PENDING);

        $armed = app(CommissionFallbackService::class)->activateOverdue(Carbon::parse('2026-06-01'));

        $this->assertSame(1, $armed); // only the lapsed owner's invoice armed
        $this->assertFalse($active->fresh()->fallback_deduction_active);
        $this->assertTrue($lapsed->fresh()->fallback_deduction_active);
    }
}
