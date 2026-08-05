<?php

namespace Tests\Feature\Affiliate;

use App\Models\AffiliateCommission;
use App\Models\AffiliateCommissionPayment;
use App\Services\AffiliateCommissionService;

/**
 * Finding #5 (2026-08-01): the period summary must be ONE upserted row per
 * (affiliate, month, year), not an append-per-event history that readers dedupe
 * with MAX(id). These lock in the one-row invariant + correct aggregation.
 */
class PeriodSummaryTest extends AffiliateDatabaseTestCase
{
    private function svc(): AffiliateCommissionService
    {
        return app(AffiliateCommissionService::class);
    }

    private function rentCommission(int $affiliateId, int $month, int $year, float $amount): void
    {
        AffiliateCommission::create([
            'affiliate_id'      => $affiliateId,
            'owner_id'          => 1,
            'source'            => AFFILIATE_COMMISSION_SOURCE_RENT,
            'commission_amount' => $amount,
            'commission_rate'   => 0.15,
            'period_month'      => $month,
            'period_year'       => $year,
        ]);
    }

    private function paymentRows(int $affiliateId, int $month, int $year): int
    {
        return AffiliateCommissionPayment::where('affiliate_id', $affiliateId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->count();
    }

    public function test_recalc_creates_one_row_and_sums_the_period(): void
    {
        $this->rentCommission(1, 3, 2026, 100);
        $this->rentCommission(1, 3, 2026, 50);

        $row = $this->svc()->recalculatePeriodSummary(1, 3, 2026);

        $this->assertSame(1, $this->paymentRows(1, 3, 2026));
        $this->assertSame(150.0, (float) $row->rent_commission_payout);
        $this->assertSame(150.0, (float) $row->total_commission_payout);
    }

    public function test_recalc_is_idempotent_stays_one_row(): void
    {
        $this->rentCommission(1, 3, 2026, 100);

        $this->svc()->recalculatePeriodSummary(1, 3, 2026);
        $this->svc()->recalculatePeriodSummary(1, 3, 2026); // second call must not add a row

        $this->assertSame(1, $this->paymentRows(1, 3, 2026));
        $this->assertSame(100.0, $this->svc()->getLifeTimeGrossCommissions(1));
    }

    public function test_recalc_updates_the_same_row_when_a_commission_is_added(): void
    {
        $this->rentCommission(1, 3, 2026, 100);
        $this->svc()->recalculatePeriodSummary(1, 3, 2026);

        $this->rentCommission(1, 3, 2026, 25); // new money lands in the period
        $this->svc()->recalculatePeriodSummary(1, 3, 2026);

        $this->assertSame(1, $this->paymentRows(1, 3, 2026)); // still one row
        $this->assertSame(125.0, $this->svc()->getLifeTimeGrossCommissions(1));
    }

    public function test_gross_sums_one_row_per_period_across_periods(): void
    {
        $this->rentCommission(1, 3, 2026, 100);
        $this->svc()->recalculatePeriodSummary(1, 3, 2026);

        $this->rentCommission(1, 4, 2026, 60);
        $this->svc()->recalculatePeriodSummary(1, 4, 2026);

        // Two distinct periods, one row each → gross is their plain sum (no dedup).
        $this->assertSame(2, AffiliateCommissionPayment::where('affiliate_id', 1)->count());
        $this->assertSame(160.0, $this->svc()->getLifeTimeGrossCommissions(1));
    }

    public function test_recalc_applies_subscription_rates(): void
    {
        config([
            'settings.FIRST_TIME_COMMISSION_RATE' => 10,
            'settings.RECURRING_COMMISSION_RATE'  => 5,
        ]);

        AffiliateCommission::create([
            'affiliate_id'        => 1,
            'owner_id'            => 1,
            'source'              => AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION,
            'type'                => NEW_CLIENT,
            'subscription_amount' => 1000,
            'period_month'        => 3,
            'period_year'         => 2026,
        ]);

        $row = $this->svc()->recalculatePeriodSummary(1, 3, 2026);

        $this->assertSame(1, $row->total_new_clients);
        $this->assertSame(100.0, (float) $row->new_commission_payout); // 10% of 1000
        $this->assertSame(100.0, (float) $row->total_commission_payout);
    }
}
