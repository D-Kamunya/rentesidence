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

    /** Distinct months that have at least one paid invoice (history depth). */
    public function cashflowHistoryMonths(int $propertyId): int
    {
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

    private function since(int $months): string
    {
        return Carbon::now()->subMonths($months)->startOfMonth()->toDateTimeString();
    }
}
