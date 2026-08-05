<?php

namespace Tests\Feature\Affiliate;

use App\Models\AffiliateCommission;
use App\Services\AffiliateCommissionService;
use App\Services\Commission\CommissionEventData;
use App\Services\Commission\PropertySalesCommissionStrategy;

/**
 * Affiliate OS WP-B — the commission-event ledger. Two guarantees:
 *  1. the property-sales strategy computes the SAME numbers the old handlers did;
 *  2. recordEvent is idempotent on (product, source, external_ref) — no double-credit.
 */
class CommissionLedgerTest extends AffiliateDatabaseTestCase
{
    private function svc(): AffiliateCommissionService
    {
        return app(AffiliateCommissionService::class);
    }

    private function strategy(): PropertySalesCommissionStrategy
    {
        return new PropertySalesCommissionStrategy();
    }

    // ── Strategy math (behaviour preserved char-for-char) ──────────────────

    public function test_subscription_new_client_uses_first_time_rate(): void
    {
        config(['settings.FIRST_TIME_COMMISSION_RATE' => 10, 'settings.RECURRING_COMMISSION_RATE' => 4]);

        $out = $this->strategy()->compute(new CommissionEventData(
            product: 'property_sales', source: AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION,
            grossAmount: 1000, clientType: NEW_CLIENT,
        ));

        $this->assertSame(10.0, $out['rate']);
        $this->assertSame(100.0, $out['commission_amount']);
        $this->assertSame('recurring', $out['cadence']);
    }

    public function test_subscription_recurring_client_uses_recurring_rate(): void
    {
        config(['settings.FIRST_TIME_COMMISSION_RATE' => 10, 'settings.RECURRING_COMMISSION_RATE' => 4]);

        $out = $this->strategy()->compute(new CommissionEventData(
            product: 'property_sales', source: AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION,
            grossAmount: 1000, clientType: RECURRING_CLIENT,
        ));

        $this->assertSame(4.0, $out['rate']);
        $this->assertSame(40.0, $out['commission_amount']);
    }

    public function test_rent_is_point_15_percent_of_gross(): void
    {
        $out = $this->strategy()->compute(new CommissionEventData(
            product: 'property_sales', source: AFFILIATE_COMMISSION_SOURCE_RENT,
            grossAmount: 100000,
        ));

        $this->assertSame(0.15, $out['rate']);
        $this->assertSame(150.0, $out['commission_amount']); // 0.15% of 100,000
    }

    public function test_marketplace_is_a_share_of_our_commission(): void
    {
        // 15% of our 200 commission = 30 (never a % of gross).
        $out = $this->strategy()->compute(new CommissionEventData(
            product: 'property_sales', source: AFFILIATE_COMMISSION_SOURCE_MARKETPLACE,
            grossAmount: 5000, ourCommission: 200, ratePercent: 15,
        ));

        $this->assertSame(15.0, $out['rate']);
        $this->assertSame(30.0, $out['commission_amount']);
        $this->assertSame('one_time', $out['cadence']);
    }

    // ── Ledger idempotency ─────────────────────────────────────────────────

    private function event(string $ref, float $amount): array
    {
        return [
            'product' => 'property_sales', 'affiliate_id' => 1, 'owner_id' => 1,
            'source' => AFFILIATE_COMMISSION_SOURCE_RENT, 'external_ref' => $ref,
            'commission_rate' => 0.15, 'commission_amount' => $amount,
            'currency' => 'KES', 'cadence' => 'recurring',
            'period_month' => 3, 'period_year' => 2026,
        ];
    }

    public function test_record_event_persists_once_and_recalcs(): void
    {
        $this->svc()->recordEvent($this->event('order-1', 150));

        $this->assertSame(1, AffiliateCommission::count());
        $this->assertSame(150.0, $this->svc()->getLifeTimeGrossCommissions(1));
    }

    public function test_record_event_is_idempotent_on_external_ref(): void
    {
        $this->svc()->recordEvent($this->event('order-1', 150));
        $this->svc()->recordEvent($this->event('order-1', 150)); // same money event re-fired

        $this->assertSame(1, AffiliateCommission::count());       // no double row
        $this->assertSame(150.0, $this->svc()->getLifeTimeGrossCommissions(1)); // no double-credit
    }

    public function test_distinct_external_refs_each_record(): void
    {
        $this->svc()->recordEvent($this->event('order-1', 150));
        $this->svc()->recordEvent($this->event('order-2', 90));

        $this->assertSame(2, AffiliateCommission::count());
        $this->assertSame(240.0, $this->svc()->getLifeTimeGrossCommissions(1));
    }
}
