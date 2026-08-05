<?php

namespace App\Centresidence\Console;

use App\Centresidence\Simulation\Sandbox;
use App\Centresidence\Simulation\SimulationHarness;
use Illuminate\Console\Command;

/**
 * Runs the Centresidence Phase 1 → Phase 3 simulation: the handbook's four §19
 * Success Criteria, end-to-end through the real engines, in an ISOLATED
 * in-memory sandbox. The live database is never touched — you can run this any
 * time to watch the whole infrastructure + commission + token + fallback flow.
 *
 *   php artisan centresidence:simulate
 */
class SimulateCommand extends Command
{
    protected $signature = 'centresidence:simulate';

    protected $description = 'Run the Centresidence simulation success criteria (handbook §19) in an isolated in-memory sandbox';

    public function handle(): int
    {
        $this->components->warn('Isolated in-memory sandbox — the live database is NOT touched.');

        Sandbox::boot('cs_sim');
        $report = (new SimulationHarness())->runAll();

        foreach ($report['cases'] as $case) {
            $this->newLine();
            $this->line("  <options=bold>{$case['key']} — {$case['title']}</>");
            $rows = array_map(
                fn ($c) => [$c['name'], $c['expected'], $c['actual'], $c['pass'] ? '<fg=green>PASS</>' : '<fg=red>FAIL</>'],
                $case['checks']
            );
            $this->table(['Check', 'Expected', 'Actual', 'Result'], $rows);
        }

        $this->newLine();
        if ($report['all_pass']) {
            $this->components->info('All simulation success criteria PASSED — Phase 1→3 gate is GREEN.');

            return self::SUCCESS;
        }

        $this->components->error('Some simulation criteria FAILED.');

        return self::FAILURE;
    }
}
