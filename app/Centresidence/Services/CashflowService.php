<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The bridge between the two ecosystems (handbook §3 "Common Connecting Layer").
 *
 * Reads the EXISTING rental system — paid invoices, property units, tenants,
 * expenses — to derive the cashflow signals finance partners underwrite on:
 * average monthly rent over N months (N is partner-configurable via
 * `required_cashflow_months`), occupancy, months of history, net cashflow, and
 * existing obligations. This is the single seam that touches legacy rental
 * tables, kept in raw query-builder form to avoid legacy model global scopes.
 */
class CashflowService
{
    /** Average monthly rent collected over the last N months (paid invoices). */
    public function averageMonthlyRent(int $propertyId, int $months): Money
    {
        $months = max(1, $months);
        $total = (string) DB::table('invoices')
            ->where('property_id', $propertyId)
            ->where('status', INVOICE_STATUS_PAID)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $this->since($months))
            ->sum('amount');

        return Money::fromDecimal($total)->prorate(1, $months); // total / months
    }

    /** Occupancy as a percentage: units with an active tenant ÷ total units. */
    public function occupancyRate(int $propertyId): float
    {
        $totalUnits = (int) DB::table('property_units')
            ->where('property_id', $propertyId)
            ->whereNull('deleted_at')
            ->count();

        if ($totalUnits === 0) {
            return 0.0;
        }

        $occupied = (int) DB::table('tenants')
            ->where('property_id', $propertyId)
            ->where('status', TENANT_STATUS_ACTIVE)
            ->whereNull('deleted_at')
            ->distinct()
            ->count('unit_id');

        return round(min($occupied, $totalUnits) / $totalUnits * 100, 2);
    }

    /**
     * Distinct months that have at least one paid invoice (history depth). Counts the
     * `billing_period` DATE (first-of-covered-month) — NOT the legacy `month` string, which
     * is a month NAME ("January") and so is year-blind (Jan-2025 and Jan-2026 collapse to one,
     * badly over/under-counting real history).
     */
    public function cashflowHistoryMonths(int $propertyId): int
    {
        if (Schema::hasColumn('invoices', 'billing_period')) {
            return (int) DB::table('invoices')
                ->where('property_id', $propertyId)
                ->where('status', INVOICE_STATUS_PAID)
                ->whereNull('deleted_at')
                ->whereNotNull('billing_period')
                ->distinct()
                ->count('billing_period');
        }

        return (int) DB::table('invoices')
            ->where('property_id', $propertyId)
            ->where('status', INVOICE_STATUS_PAID)
            ->whereNull('deleted_at')
            ->distinct()
            ->count('month');
    }

    /** Average monthly expenses over the last N months. */
    public function averageMonthlyExpenses(int $propertyId, int $months): Money
    {
        $months = max(1, $months);
        $total = (string) DB::table('expenses')
            ->where('property_id', $propertyId)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $this->since($months))
            ->sum('total_amount');

        return Money::fromDecimal($total)->prorate(1, $months);
    }

    /** Average monthly net cashflow = rent − expenses. */
    public function netMonthlyCashflow(int $propertyId, int $months): Money
    {
        return $this->averageMonthlyRent($propertyId, $months)
            ->minus($this->averageMonthlyExpenses($propertyId, $months));
    }

    /** Existing monthly obligations = sum of active Centresidence facilities' targets. */
    public function existingMonthlyObligations(int $propertyId): Money
    {
        if (! Schema::hasTable('finance_facilities')) {
            return Money::zero();
        }

        $total = (string) DB::table('finance_facilities')
            ->where('property_id', $propertyId)
            ->where('status', 'active')
            ->sum('monthly_target');

        return Money::fromDecimal($total);
    }

    /**
     * Assemble the underwriting context for an application, including derived
     * ratios the rules can reference by parameter name. The lookback window is
     * the partner's required_cashflow_months (min 1).
     *
     * @return array<string,mixed>
     */
    public function underwritingContext(FinanceApplication $application): array
    {
        $propertyId = (int) $application->property_id;
        $months = max(1, (int) optional($application->partnerModule)->required_cashflow_months);

        $grossRent = $this->averageMonthlyRent($propertyId, $months);
        $net = $this->netMonthlyCashflow($propertyId, $months);
        $obligations = $this->existingMonthlyObligations($propertyId);
        $repayment = Money::fromDecimal($application->estimated_monthly_repayment ?? '0');

        $grossF = $grossRent->toFloat();
        $repayF = $repayment->toFloat();

        return [
            'occupancy_rate' => $this->occupancyRate($propertyId),
            'gross_rent' => $grossF,
            'net_cashflow' => $net->toFloat(),
            'cashflow_history_months' => $this->cashflowHistoryMonths($propertyId),
            'existing_obligations' => $obligations->toFloat(),
            'monthly_repayment' => $repayF,
            // Derived ratios (rules reference these parameter names).
            'net_cashflow_to_repayment' => $repayF > 0 ? round($net->toFloat() / $repayF, 4) : 999,
            'obligations_to_rent' => $grossF > 0 ? round(($obligations->toFloat() + $repayF) / $grossF, 4) : 999,
        ];
    }

    /**
     * A presentation snapshot for the finance-partner application review — the SAME per-property
     * paid-invoice basis the underwriting rules use (so the panel and the Eligibility check never
     * disagree), enriched with the spread + a stability signal + a monthly series that make an
     * underwriting call honest rather than a lone rosy average. Default window = the partner's
     * required_cashflow_months (resolved by the caller); financier may override on the page.
     *
     * @return array<string,mixed>
     */
    public function presentationSnapshot(int $propertyId, int $months): array
    {
        $months = max(1, min(36, $months));

        // Per-month collected — same table + filters as averageMonthlyRent(), bucketed for display.
        $byMonth = [];
        DB::table('invoices')
            ->where('property_id', $propertyId)
            ->where('status', INVOICE_STATUS_PAID)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $this->since($months))
            ->get(['amount', 'created_at'])
            ->each(function ($r) use (&$byMonth) {
                $key = Carbon::parse($r->created_at)->format('Y-m');
                $byMonth[$key] = ($byMonth[$key] ?? 0) + (float) $r->amount;
            });

        $monthly = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $d = Carbon::now()->startOfMonth()->subMonths($i);
            $monthly[] = [
                'label'     => $d->format('M Y'),
                'collected' => round((float) ($byMonth[$d->format('Y-m')] ?? 0), 2),
            ];
        }

        $values   = array_column($monthly, 'collected');
        $withData = array_values(array_filter($values, fn ($v) => $v > 0));
        // Headline average = the SAME figure the rules underwrite on (total / N prorate).
        $average  = $this->averageMonthlyRent($propertyId, $months)->toFloat();

        $totalUnits = (int) DB::table('property_units')
            ->where('property_id', $propertyId)->whereNull('deleted_at')->count();
        $occupied = (int) DB::table('tenants')
            ->where('property_id', $propertyId)
            ->where('status', TENANT_STATUS_ACTIVE)
            ->whereNull('deleted_at')->distinct()->count('unit_id');
        $occupied = $totalUnits > 0 ? min($occupied, $totalUnits) : $occupied;

        return [
            'months'           => $months,
            'monthly'          => $monthly,
            'average'          => round($average, 2),
            'min'              => $withData ? round(min($withData), 2) : 0.0,
            'max'              => $values ? round(max($values), 2) : 0.0,
            'months_with_data' => count($withData),
            'history_months'   => $this->cashflowHistoryMonths($propertyId),
            'stability_score'  => $this->stabilityScore($values),
            'occupancy'        => [
                'occupied' => $occupied,
                'total'    => $totalUnits,
                'rate'     => $totalUnits > 0 ? (int) round($occupied / $totalUnits * 100) : 0,
            ],
        ];
    }

    /**
     * 0–100 steadiness of the month-to-month series (100 = flat/reliable, lower = lumpy), from the
     * coefficient of variation — sits next to the average so a spiky history can't hide behind it.
     */
    private function stabilityScore(array $values): int
    {
        $mean = count($values) ? array_sum($values) / count($values) : 0.0;
        if ($mean <= 0 || count($values) < 2) {
            return 0;
        }
        $variance = 0.0;
        foreach ($values as $v) {
            $variance += ($v - $mean) ** 2;
        }
        return (int) max(0, min(100, round((1 - sqrt($variance / count($values)) / $mean) * 100)));
    }

    private function since(int $months): string
    {
        return Carbon::now()->subMonths($months)->startOfMonth()->toDateTimeString();
    }
}
