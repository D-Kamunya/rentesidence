<?php

namespace Tests\Feature\Affiliate;

use App\Models\AffiliateCommissionPayment;
use App\Models\AffiliateWithdrawal;
use App\Services\AffiliateCommissionService;

/**
 * Tier-1 money-safety fix (2026-07-31): available balance must reserve PENDING
 * withdrawals, not only APPROVED ones — otherwise an affiliate could stack
 * pending requests that each pass the balance check but together over-draw.
 */
class WithdrawalBalanceTest extends AffiliateDatabaseTestCase
{
    private function svc(): AffiliateCommissionService
    {
        return app(AffiliateCommissionService::class);
    }

    private function earn(int $affiliateId, float $amount): void
    {
        AffiliateCommissionPayment::create([
            'affiliate_id' => $affiliateId,
            'period_month' => 1,
            'period_year' => 2026,
            'total_commission_payout' => $amount,
        ]);
    }

    private function withdrawal(int $affiliateId, float $amount, int $status): void
    {
        AffiliateWithdrawal::create([
            'affiliate_id' => $affiliateId,
            'amount' => $amount,
            'status' => $status,
            'settlement_method' => 'b2c',
        ]);
    }

    public function test_pending_withdrawal_is_reserved_against_available_balance(): void
    {
        $this->earn(1, 1000);
        $this->withdrawal(1, 300, AFFILIATE_WITHDRAWAL_PENDING);

        // The old bug: pending was ignored, so available stayed 1000 → over-draw.
        $this->assertSame(300.0, $this->svc()->getReservedWithdrawals(1));
        $this->assertSame(700.0, $this->svc()->getAvailableBalance(1));
    }

    public function test_stacked_pending_requests_cannot_exceed_balance(): void
    {
        $this->earn(1, 1000);
        // First pending request of 700 leaves 300 available — a second 700 must NOT fit.
        $this->withdrawal(1, 700, AFFILIATE_WITHDRAWAL_PENDING);
        $available = $this->svc()->getAvailableBalance(1);

        $this->assertSame(300.0, $available);
        $this->assertLessThan(700.0, $available); // the second stacked request is refused
    }

    public function test_pending_and_approved_are_both_reserved(): void
    {
        $this->earn(1, 1000);
        $this->withdrawal(1, 300, AFFILIATE_WITHDRAWAL_PENDING);
        $this->withdrawal(1, 200, AFFILIATE_WITHDRAWAL_APPROVED);

        $this->assertSame(500.0, $this->svc()->getReservedWithdrawals(1));
        $this->assertSame(500.0, $this->svc()->getAvailableBalance(1));
    }

    public function test_rejected_withdrawal_restores_availability(): void
    {
        $this->earn(1, 1000);
        $this->withdrawal(1, 400, AFFILIATE_WITHDRAWAL_REJECTED);

        $this->assertSame(0.0, $this->svc()->getReservedWithdrawals(1));
        $this->assertSame(1000.0, $this->svc()->getAvailableBalance(1));
    }

    public function test_gross_is_unaffected_by_reservations(): void
    {
        $this->earn(1, 1000);
        $this->withdrawal(1, 300, AFFILIATE_WITHDRAWAL_PENDING);

        $this->assertSame(1000.0, $this->svc()->getLifeTimeGrossCommissions(1));
    }
}
