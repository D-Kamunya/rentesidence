<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\FinanceAnalyticsSnapshot;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Finance Cashflow Analytics (handbook §9.9) — computes a daily portfolio
 * snapshot: outstanding balances, expected vs collected, collection/default
 * rates, and platform-fee revenue. Idempotent per date (upsert).
 */
class FinanceAnalyticsService
{
    public function takeSnapshot(?Carbon $date = null): FinanceAnalyticsSnapshot
    {
        $date = ($date ?? Carbon::now())->copy();
        $monthStart = $date->copy()->startOfMonth();
        $yearStart = $date->copy()->startOfYear();

        $active = FinanceFacility::query()->where('status', FinanceFacility::STATUS_ACTIVE);

        $totalActive = (clone $active)->count();
        $outPrincipal = (string) (clone $active)->sum('outstanding_principal');
        $outInterest = (string) (clone $active)->sum('outstanding_interest');
        $outPenalty = (string) (clone $active)->sum('outstanding_penalty');
        $expectedMonthly = (string) (clone $active)->sum('monthly_target');

        // Collected this month from facility repayment transactions.
        $collectedMonth = (string) DB::table('facility_transactions')
            ->whereIn('transaction_type', ['repayment_principal', 'repayment_interest', 'repayment_penalty'])
            ->where('created_at', '>=', $monthStart->toDateTimeString())
            ->sum('amount');

        $defaulted = FinanceFacility::query()->where('status', FinanceFacility::STATUS_DEFAULTED)->count();

        $expectedF = (float) $expectedMonthly;
        $collectionRate = $expectedF > 0 ? round(((float) $collectedMonth) / $expectedF * 100, 2) : 0;
        $totalForDefaultRate = $totalActive + $defaulted;
        $defaultRate = $totalForDefaultRate > 0 ? round($defaulted / $totalForDefaultRate * 100, 2) : 0;

        $feesMonth = (string) FinanceFacility::query()
            ->whereDate('disbursement_date', '>=', $monthStart->toDateString())
            ->sum('platform_fee_amount');
        $feesYtd = (string) FinanceFacility::query()
            ->whereDate('disbursement_date', '>=', $yearStart->toDateString())
            ->sum('platform_fee_amount');

        $avgRate = (float) ((clone $active)->avg('interest_rate') ?? 0);

        return FinanceAnalyticsSnapshot::updateOrCreate(
            // Match on the Carbon (start of day) so the lookup equals the stored
            // datetime on re-run (the date cast persists Y-m-d 00:00:00).
            ['snapshot_date' => $date->copy()->startOfDay()],
            [
                'total_active_facilities' => $totalActive,
                'total_outstanding_principal' => Money::fromDecimal($outPrincipal)->toDecimal(),
                'total_outstanding_interest' => Money::fromDecimal($outInterest)->toDecimal(),
                'total_outstanding_penalty' => Money::fromDecimal($outPenalty)->toDecimal(),
                'total_expected_monthly' => Money::fromDecimal($expectedMonthly)->toDecimal(),
                'total_collected_month' => Money::fromDecimal($collectedMonth)->toDecimal(),
                'collection_rate' => $collectionRate,
                'facilities_in_default' => $defaulted,
                'default_rate' => $defaultRate,
                'total_platform_fees_month' => Money::fromDecimal($feesMonth)->toDecimal(),
                'total_platform_fees_ytd' => Money::fromDecimal($feesYtd)->toDecimal(),
                'average_interest_rate' => round($avgRate, 2),
                'created_at' => Carbon::now(),
            ]
        );
    }
}
