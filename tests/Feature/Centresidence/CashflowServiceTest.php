<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Services\CashflowService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Verifies the bridge to the existing rental system: average rent, occupancy,
 * history depth and net cashflow are read from legacy invoices/tenants/expenses.
 */
class CashflowServiceTest extends CentresidenceDatabaseTestCase
{
    private int $propertyId = 50;

    protected function setUp(): void
    {
        parent::setUp();

        // 4 units, 3 with active tenants → 75% occupancy.
        foreach (range(101, 104) as $unitId) {
            DB::table('property_units')->insert(['id' => $unitId, 'property_id' => $this->propertyId, 'name' => "U$unitId"]);
        }
        DB::table('tenants')->insert([
            ['property_id' => $this->propertyId, 'unit_id' => 101, 'status' => TENANT_STATUS_ACTIVE],
            ['property_id' => $this->propertyId, 'unit_id' => 102, 'status' => TENANT_STATUS_ACTIVE],
            ['property_id' => $this->propertyId, 'unit_id' => 103, 'status' => TENANT_STATUS_ACTIVE],
            ['property_id' => $this->propertyId, 'unit_id' => 104, 'status' => TENANT_STATUS_INACTIVE],
        ]);

        // 3 paid rent invoices (one per month) + 1 pending (excluded).
        DB::table('invoices')->insert([
            ['property_id' => $this->propertyId, 'month' => '2026-04', 'amount' => 100000, 'status' => INVOICE_STATUS_PAID, 'created_at' => Carbon::now()->subMonths(2)],
            ['property_id' => $this->propertyId, 'month' => '2026-05', 'amount' => 100000, 'status' => INVOICE_STATUS_PAID, 'created_at' => Carbon::now()->subMonth()],
            ['property_id' => $this->propertyId, 'month' => '2026-06', 'amount' => 100000, 'status' => INVOICE_STATUS_PAID, 'created_at' => Carbon::now()],
            ['property_id' => $this->propertyId, 'month' => '2026-06', 'amount' => 50000, 'status' => INVOICE_STATUS_PENDING, 'created_at' => Carbon::now()],
        ]);

        // Expenses 30,000 over the window → 10,000/month avg.
        DB::table('expenses')->insert([
            ['property_id' => $this->propertyId, 'total_amount' => 30000, 'created_at' => Carbon::now()->subMonth()],
        ]);
    }

    public function test_average_monthly_rent_excludes_unpaid(): void
    {
        // 300,000 paid over 3 months = 100,000/month (the 50,000 pending excluded).
        $this->assertSame('100000.00', app(CashflowService::class)->averageMonthlyRent($this->propertyId, 3)->toDecimal());
    }

    public function test_occupancy_rate(): void
    {
        $this->assertSame(75.0, app(CashflowService::class)->occupancyRate($this->propertyId));
    }

    public function test_cashflow_history_months(): void
    {
        $this->assertSame(3, app(CashflowService::class)->cashflowHistoryMonths($this->propertyId));
    }

    public function test_net_monthly_cashflow(): void
    {
        // 100,000 rent − 10,000 expenses = 90,000.
        $this->assertSame('90000.00', app(CashflowService::class)->netMonthlyCashflow($this->propertyId, 3)->toDecimal());
    }
}
