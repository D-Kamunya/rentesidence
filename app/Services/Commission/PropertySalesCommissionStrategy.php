<?php

namespace App\Services\Commission;

use App\Services\AffiliateCommissionService;

/**
 * The original Centresidence property-management commission rules, extracted
 * verbatim from AffiliateCommissionService so behaviour is unchanged — this is
 * just the seam that lets other products supply their own rules later.
 *
 *  - subscription: NEW client → FIRST_TIME_COMMISSION_RATE, else RECURRING_COMMISSION_RATE,
 *                  applied to the subscription amount.
 *  - rent:         15% of our 1% = a flat 0.15% of the gross rent.
 *  - marketplace:  a share (category rate) of OUR commission on the sale — a true
 *                  cut, so we never pay more than we earned.
 */
class PropertySalesCommissionStrategy implements CommissionRuleStrategy
{
    public function compute(CommissionEventData $event): array
    {
        return match ($event->source) {
            AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION => $this->subscription($event),
            AFFILIATE_COMMISSION_SOURCE_RENT         => $this->rent($event),
            AFFILIATE_COMMISSION_SOURCE_MARKETPLACE  => $this->marketplace($event),
            default => ['rate' => 0.0, 'commission_amount' => 0.0, 'cadence' => 'one_time'],
        };
    }

    public function currency(): string
    {
        return (string) (config('affiliate_os.products.property_sales.currency') ?? 'KES');
    }

    private function subscription(CommissionEventData $event): array
    {
        $rate = $event->clientType === NEW_CLIENT
            ? (float) getOption('FIRST_TIME_COMMISSION_RATE')
            : (float) getOption('RECURRING_COMMISSION_RATE');

        return [
            'rate'              => $rate,
            'commission_amount' => round($event->grossAmount * ($rate / 100), 2),
            'cadence'           => 'recurring',
        ];
    }

    private function rent(CommissionEventData $event): array
    {
        $rate = 0.15; // 15% of the 1% Centresidence fee = 0.15% of gross

        return [
            'rate'              => $rate,
            'commission_amount' => round($event->grossAmount * ($rate / 100), 2),
            'cadence'           => 'recurring',
        ];
    }

    private function marketplace(CommissionEventData $event): array
    {
        return [
            'rate'              => $event->ratePercent,
            'commission_amount' => AffiliateCommissionService::scopedMarketplaceCommission(
                (float) $event->ourCommission,
                $event->ratePercent
            ),
            'cadence'           => 'one_time',
        ];
    }
}
