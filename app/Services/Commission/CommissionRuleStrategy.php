<?php

namespace App\Services\Commission;

/**
 * Per-product commission rules (docs/affiliate-os-design.md §3.4/§4). Each product
 * (spoke) supplies one; the OS ledger stays product-agnostic and just persists what
 * the strategy computes. THE seam that lets four different economies share one ledger.
 */
interface CommissionRuleStrategy
{
    /**
     * Compute the affiliate commission for a collected-money event.
     *
     * @return array{rate: float, commission_amount: float, cadence: string}
     */
    public function compute(CommissionEventData $event): array;

    /** Settlement currency for this product. */
    public function currency(): string;
}
