<?php

namespace Tests\Unit;

use App\Services\AffiliateCommissionService;
use Tests\TestCase;

/**
 * Finding #4 fix — the marketplace affiliate cut must be a share of Centresidence's
 * OWN commission on the sale (like rent's "15% of our 1%"), never a % of gross,
 * so an affiliate can never be paid more than we earned on that sale.
 */
class AffiliateCommissionScopingTest extends TestCase
{
    public function test_cut_is_a_share_of_our_commission(): void
    {
        // Sale KES 10,000; our commission @3% = 300. Category affiliate rate 15%.
        $affiliate = AffiliateCommissionService::scopedMarketplaceCommission(300.0, 15);

        $this->assertSame(45.0, $affiliate);              // 15% of our 300
        $this->assertLessThan(300.0, $affiliate);         // never exceeds our take
    }

    public function test_affiliate_can_never_exceed_our_commission(): void
    {
        // Even at a 100% category rate the affiliate only gets ALL of our commission,
        // never more. (The old model paid 15% of the 10,000 gross = 1,500 — 5x our earnings.)
        $this->assertSame(300.0, AffiliateCommissionService::scopedMarketplaceCommission(300.0, 100));
    }

    public function test_zero_or_negative_inputs_yield_zero(): void
    {
        $this->assertSame(0.0, AffiliateCommissionService::scopedMarketplaceCommission(0.0, 15));
        $this->assertSame(0.0, AffiliateCommissionService::scopedMarketplaceCommission(300.0, 0));
        $this->assertSame(0.0, AffiliateCommissionService::scopedMarketplaceCommission(-50.0, 15));
    }

    public function test_matches_rent_style_share_at_15_percent(): void
    {
        // Rent pays the affiliate 15% of our 1%. Marketplace at a 15% category rate
        // pays 15% of our commission — the same "share of our take" shape.
        $ourCommission = 1000.0;
        $this->assertSame(150.0, AffiliateCommissionService::scopedMarketplaceCommission($ourCommission, 15));
    }
}
