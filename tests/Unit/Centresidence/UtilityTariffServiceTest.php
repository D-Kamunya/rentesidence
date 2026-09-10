<?php

namespace Tests\Unit\Centresidence;

use App\Centresidence\Services\UtilityTariffService;
use Tests\TestCase;

/**
 * Owner-set utility tariff guardrails (token-tariff-ownership, 2026-09-10):
 *   - HARD system-integrity floor: owner revenue >= 0 (tariff covers our commission).
 *   - ADVISORY regulatory ceiling: informational only, never blocks; carries the indemnity line.
 */
class UtilityTariffServiceTest extends TestCase
{
    private UtilityTariffService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = new UtilityTariffService();
        // Deterministic references for the assertions below.
        config(['centresidence.utility_tariffs.references.water.counties' => ['nairobi' => 0.110]]);
        config(['centresidence.utility_tariffs.references.water.default' => null]);
        config(['centresidence.utility_tariffs.references.gas.default' => null]);
        config(['centresidence.utility_tariffs.advisory_tolerance' => 1.0]);
    }

    /** @test */
    public function it_classifies_utilities_by_module_key(): void
    {
        $this->assertSame('water', $this->svc->utilityClass('water_meter'));
        $this->assertSame('gas', $this->svc->utilityClass('gas_meter'));
        $this->assertSame('other', $this->svc->utilityClass('smart_lock'));
        $this->assertSame('other', $this->svc->utilityClass(null));
    }

    /** @test */
    public function the_floor_requires_owner_revenue_to_stay_non_negative(): void
    {
        // Water (no commission): any positive tariff clears the floor; zero does not.
        $this->assertTrue($this->svc->meetsFloor('5', '0'));
        $this->assertFalse($this->svc->meetsFloor('0', '0'));

        // Gas (commission 0.02/unit): price must stay >= commission.
        $this->assertTrue($this->svc->meetsFloor('5', '0.02'));    // price 0.20 >= 0.02 → owner rev 0.18
        $this->assertFalse($this->svc->meetsFloor('100', '0.02')); // price 0.01 < 0.02 → owner rev negative
    }

    /** @test */
    public function price_per_unit_is_the_inverse_of_units_per_kes(): void
    {
        $this->assertEqualsWithDelta(0.20, $this->svc->pricePerUnit('5'), 0.0001);
        $this->assertSame(0.0, $this->svc->pricePerUnit('0'));
    }

    /** @test */
    public function advisory_warns_only_when_a_known_reference_is_exceeded(): void
    {
        // Nairobi water reference 0.11/litre; a 0.20/litre tariff exceeds it → warning.
        $over = $this->svc->advisory(0.20, 'water', 'nairobi');
        $this->assertSame('warning', $over['level']);
        $this->assertStringContainsString('solely responsible', $over['message']);

        // Below the reference → gentle info (still carries the compliance/indemnity line).
        $under = $this->svc->advisory(0.05, 'water', 'nairobi');
        $this->assertSame('info', $under['level']);

        // No reference (gas has none configured, unknown county) → info, never a hard stop.
        $this->assertSame('info', $this->svc->advisory(999.0, 'gas')['level']);
        $this->assertSame('info', $this->svc->advisory(0.20, 'water', 'someplace')['level']);
    }

    /** @test */
    public function reference_lookup_is_county_aware_for_water(): void
    {
        $this->assertEqualsWithDelta(0.110, $this->svc->reference('water', 'nairobi'), 0.0001);
        $this->assertNull($this->svc->reference('water', 'unknown-county'));
        $this->assertNull($this->svc->reference('gas'));
    }
}
