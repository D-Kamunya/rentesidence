<?php

namespace App\Centresidence\Services;

use App\Centresidence\Support\Money;

/**
 * Auto-calculates the facility maths for a financing application
 * (handbook §9.3 / §2 "Select & Calculate"):
 *
 *   base_cost      = unit_price × quantity
 *   platform_fee   = base_cost × platform_fee_percentage
 *   requested      = base_cost + platform_fee   (the finance partner underwrites
 *                                                this TOTAL, fee included)
 *   est. monthly   = amortised over the tenor at the partner's rate
 *
 * Monetary results use the exact Money object; the monthly estimate uses a
 * standard amortisation formula (float math is acceptable for an *estimate* and
 * is rounded to cents on the way out).
 */
class FinancingCalculator
{
    /**
     * @return array{base_cost:string, platform_fee_amount:string, requested_amount:string, estimated_monthly_repayment:string}
     */
    public function compute(
        string $unitPrice,
        int $quantity,
        string $platformFeePercentage,
        string $annualInterestRate,
        int $months,
        string $method = 'reducing_balance'
    ): array {
        $base = Money::fromDecimal($unitPrice)->timesQuantity($quantity);
        $platformFee = $base->percentage($platformFeePercentage);
        $requested = $base->plus($platformFee);

        $monthly = $this->estimatedMonthly($requested, $annualInterestRate, $months, $method);

        return [
            'base_cost' => $base->toDecimal(),
            'platform_fee_amount' => $platformFee->toDecimal(),
            'requested_amount' => $requested->toDecimal(),
            'estimated_monthly_repayment' => $monthly->toDecimal(),
        ];
    }

    /** Monthly repayment for a known principal — used by the Facility engine. */
    public function monthlyRepayment(string $principal, string $annualRatePercent, int $months, string $method = 'reducing_balance'): Money
    {
        return $this->estimatedMonthly(Money::fromDecimal($principal), $annualRatePercent, $months, $method);
    }

    private function estimatedMonthly(Money $principal, string $annualRatePercent, int $months, string $method): Money
    {
        if ($months <= 0) {
            return Money::zero();
        }

        $p = $principal->toFloat();
        $annualRate = (float) $annualRatePercent / 100;

        if ($method === 'flat') {
            // Flat interest over the whole tenor.
            $interest = $p * $annualRate * ($months / 12);
            $monthly = ($p + $interest) / $months;

            return Money::fromDecimal(number_format($monthly, 2, '.', ''));
        }

        // Reducing-balance amortisation.
        $r = $annualRate / 12;
        if ($r <= 0.0) {
            $monthly = $p / $months;
        } else {
            $monthly = $p * $r / (1 - pow(1 + $r, -$months));
        }

        return Money::fromDecimal(number_format($monthly, 2, '.', ''));
    }
}
