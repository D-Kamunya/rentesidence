<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Simulation\Sandbox;
use App\Centresidence\Simulation\SimulationHarness;
use Tests\TestCase;

/**
 * THE PHASE 1 → PHASE 3 GATE.
 *
 * Runs the full simulation harness — the same code path the
 * `centresidence:simulate` command uses — and asserts that all four handbook
 * §19 Simulation Success Criteria pass. If this is green, the multi-owner
 * topology, composable commission, embedded token economics and tenant-
 * protective fallback are proven end-to-end and the platform may proceed to the
 * Finance ecosystem (WP6+).
 *
 * Boots its own sandbox (no legacy seeding) since the harness seeds its own
 * actors, so it does not extend CentresidenceDatabaseTestCase.
 */
class SimulationSuccessCriteriaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Sandbox::boot('cs_sqlite');
    }

    public function test_all_four_handbook_success_criteria_pass(): void
    {
        $report = (new SimulationHarness())->runAll();

        // Per-case assertions with readable failure messages.
        foreach ($report['cases'] as $case) {
            foreach ($case['checks'] as $check) {
                $this->assertTrue(
                    $check['pass'],
                    "{$case['key']} — {$check['name']}: expected {$check['expected']}, got {$check['actual']}"
                );
            }
        }

        $this->assertTrue($report['all_pass'], 'All handbook §19 success criteria must pass (Phase 1→3 gate).');
        $this->assertCount(4, $report['cases']);
    }
}
