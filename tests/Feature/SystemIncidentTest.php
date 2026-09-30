<?php

namespace Tests\Feature;

use App\Models\SystemIncident;
use App\Services\SystemIncidentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Locks the incident recorder's core discipline: aggregation (repeats fold onto one
 * row, not spam), the warn-until-repeated escalation, and resolve clearing the badge.
 *
 * Isolated in-memory sqlite harness (same pattern as AffiliateDatabaseTestCase) — the
 * full migration set has MySQL-only ALTERs that don't run on sqlite, so we build just
 * the one table this service touches.
 */
class SystemIncidentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'incident_sqlite',
            'database.connections.incident_sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('incident_sqlite');
        DB::setDefaultConnection('incident_sqlite');

        Schema::create('system_incidents', function ($t) {
            $t->id();
            $t->string('type', 40);
            $t->string('severity', 12)->default('warning');
            $t->string('status', 16)->default('open');
            $t->string('title');
            $t->text('message')->nullable();
            $t->json('context')->nullable();
            $t->string('dedup_key')->nullable();
            $t->unsignedInteger('occurrences')->default(1);
            $t->timestamp('first_seen_at')->nullable();
            $t->timestamp('last_seen_at')->nullable();
            $t->timestamp('alerted_at')->nullable();
            $t->unsignedBigInteger('acknowledged_by')->nullable();
            $t->unsignedBigInteger('resolved_by')->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
        });

        Queue::fake(); // never actually page during tests
    }

    public function test_report_creates_a_single_incident(): void
    {
        $svc = app(SystemIncidentService::class);

        $incident = $svc->report(
            SystemIncident::TYPE_PAYOUT_FAILURE,
            SystemIncident::SEVERITY_CRITICAL,
            'Payout failed',
            'affiliate #1 declined'
        );

        $this->assertNotNull($incident);
        $this->assertSame(1, $incident->occurrences);
        $this->assertSame('open', $incident->status);
        $this->assertSame(1, SystemIncident::count());
        $this->assertSame(1, $svc->openCount());
    }

    public function test_same_dedup_key_aggregates_instead_of_duplicating(): void
    {
        $svc = app(SystemIncidentService::class);

        $svc->report('payout_failure', 'critical', 'X', 'a', [], 'k1');
        $svc->report('payout_failure', 'critical', 'X', 'b', [], 'k1');
        $svc->report('payout_failure', 'critical', 'X', 'c', [], 'k1');

        $this->assertSame(1, SystemIncident::count());
        $this->assertSame(3, SystemIncident::first()->occurrences);
    }

    public function test_warn_until_repeated_escalates_to_critical_at_threshold(): void
    {
        $svc = app(SystemIncidentService::class);

        // First four are warnings; the fifth escalates to critical.
        for ($i = 1; $i <= 4; $i++) {
            $svc->report('callback_rejected', 'warning', 'Forged callbacks', '', [], 'callback_rejected', 5, 5);
        }
        $this->assertSame('warning', SystemIncident::first()->severity);

        $svc->report('callback_rejected', 'warning', 'Forged callbacks', '', [], 'callback_rejected', 5, 5);
        $incident = SystemIncident::first();
        $this->assertSame('critical', $incident->severity);
        $this->assertSame(5, $incident->occurrences);
    }

    public function test_resolve_clears_the_open_count_and_a_repeat_reopens(): void
    {
        $svc = app(SystemIncidentService::class);

        $incident = $svc->report('job_failed', 'critical', 'Job died', '', [], 'job:X');
        $this->assertSame(1, $svc->openCount());

        $incident->update(['status' => SystemIncident::STATUS_RESOLVED]);
        $this->assertSame(0, $svc->openCount());

        // Same fault happens again → a new open row (the old one stays resolved).
        $svc->report('job_failed', 'critical', 'Job died', '', [], 'job:X');
        $this->assertSame(1, $svc->openCount());
    }
}
