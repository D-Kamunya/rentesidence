<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\RepaymentSchedule;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;

/**
 * Builds an amortisation schedule for a principal over a tenor — shared by
 * facility creation (WP7) and restructuring (WP9) so the maths lives in one
 * place. Supports reducing-balance and flat interest; the principal amortises
 * exactly (the final period absorbs rounding).
 */
class RepaymentScheduleBuilder
{
    public function __construct(private FinancingCalculator $calculator)
    {
    }

    /**
     * @return array{rows:array, monthly:Money, total_repayable:Money}
     */
    public function build(Money $principal, string $annualRate, int $months, string $method, ?Carbon $startFrom = null): array
    {
        $months = max(1, $months);
        $start = $startFrom ? $startFrom->copy() : Carbon::now();
        $monthly = $this->calculator->monthlyRepayment($principal->toDecimal(), $annualRate, $months, $method);

        $rows = [];
        $balance = $principal;
        $totalRepayable = Money::zero();
        $rFloat = (float) $annualRate / 100 / 12;
        $isFlat = $method === 'flat';

        $flatPrincipal = $isFlat ? $principal->prorate(1, $months) : null;
        $flatInterest = null;
        if ($isFlat) {
            $totalInterest = Money::fromDecimal(number_format($principal->toFloat() * ((float) $annualRate / 100) * ($months / 12), 2, '.', ''));
            $flatInterest = $totalInterest->prorate(1, $months);
        }

        for ($i = 1; $i <= $months; $i++) {
            $opening = $balance;

            if ($isFlat) {
                $interest = $flatInterest;
                $principalDue = ($i === $months) ? $balance : $flatPrincipal;
            } else {
                $interest = Money::fromDecimal(number_format($balance->toFloat() * $rFloat, 2, '.', ''));
                $principalDue = ($i === $months) ? $balance : $monthly->minus($interest);
            }

            $totalDue = $principalDue->plus($interest);
            $closing = $balance->minus($principalDue);

            $rows[] = [
                'period_number' => $i,
                'due_date' => $start->copy()->addMonths($i)->toDateString(),
                'opening_balance' => $opening->toDecimal(),
                'principal_due' => $principalDue->toDecimal(),
                'interest_due' => $interest->toDecimal(),
                'total_due' => $totalDue->toDecimal(),
                'closing_balance' => $closing->toDecimal(),
                'status' => RepaymentSchedule::STATUS_PENDING,
            ];

            $totalRepayable = $totalRepayable->plus($totalDue);
            $balance = $closing;
        }

        return ['rows' => $rows, 'monthly' => $monthly, 'total_repayable' => $totalRepayable];
    }
}
