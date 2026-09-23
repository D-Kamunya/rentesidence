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
class PropertyManagementCommissionStrategy implements CommissionRuleStrategy
{
    public function compute(CommissionEventData $event): array
    {
        return match ($event->source) {
            AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION => $this->subscription($event),
            AFFILIATE_COMMISSION_SOURCE_RENT         => $this->rent($event),
            AFFILIATE_COMMISSION_SOURCE_MARKETPLACE  => $this->marketplace($event),
            // Usage lines — screening / agreement / gas token / financing origination.
            AFFILIATE_COMMISSION_SOURCE_SCREENING,
            AFFILIATE_COMMISSION_SOURCE_AGREEMENT,
            AFFILIATE_COMMISSION_SOURCE_GAS_TOKEN,
            AFFILIATE_COMMISSION_SOURCE_FINANCING    => $this->usageCut($event),
            default => ['rate' => 0.0, 'commission_amount' => 0.0, 'cadence' => 'one_time'],
        };
    }

    /**
     * A cut of OUR take on a usage event (never more than we earned), reusing the SAME
     * first-time/recurring config as subscription — the affiliate earnings just span more
     * lines now. First event of a (owner, source) = first-time bounty rate; then recurring.
     */
    private function usageCut(CommissionEventData $event): array
    {
        $rate = $event->clientType === NEW_CLIENT
            ? (float) getOption('FIRST_TIME_COMMISSION_RATE')
            : (float) getOption('RECURRING_COMMISSION_RATE');

        $ourTake = max(0.0, (float) ($event->ourCommission ?? 0));

        return [
            'rate'              => $rate,
            'commission_amount' => round($ourTake * ($rate / 100), 2),
            'cadence'           => 'recurring',
        ];
    }

    public function currency(): string
    {
        return (string) (config('affiliate_os.products.property_management.currency') ?? 'KES');
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
