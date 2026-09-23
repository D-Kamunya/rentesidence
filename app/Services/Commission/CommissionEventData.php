<?php

namespace App\Services\Commission;

/**
 * A collected-money event the OS turns into an affiliate commission
 * (docs/affiliate-os-design.md §4). Product-agnostic: the same shape carries a
 * subscription charge, a rent payment, or a marketplace sale. The per-product
 * CommissionRuleStrategy reads it and returns the commission. Immutable inputs.
 */
class CommissionEventData
{
    public function __construct(
        public string $product,
        public string $source,          // subscription | rent | marketplace
        public float $grossAmount,      // the money actually collected
        public ?float $ourCommission = null,  // our take on it (marketplace/rent-style cuts)
        public int $clientType = NEW_CLIENT,  // subscription: NEW_CLIENT | RECURRING_CLIENT
        public float $ratePercent = 0.0,      // marketplace: category rate (% of our take)
    ) {}
}
