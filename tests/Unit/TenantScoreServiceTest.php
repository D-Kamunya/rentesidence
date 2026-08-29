<?php

namespace Tests\Unit;

use App\Services\Screening\TenantScoreService;
use Tests\TestCase;

/**
 * The rental score model is a pure function of the metrics, so these run with no DB. They pin
 * the properties that matter for fairness + defensibility: behaviour drives the score, thin
 * files regress to the mean (no over-branding either way), and delinquency scores low.
 */
class TenantScoreServiceTest extends TestCase
{
    private function score(array $overrides): array
    {
        $base = [
            'invoices_total' => 0, 'invoices_paid' => 0, 'overdue_count' => 0,
            'total_billed' => 0, 'outstanding' => 0, 'on_time_rate' => null, 'avg_days_late' => null,
        ];
        return (new TenantScoreService())->score(array_merge($base, $overrides));
    }

    public function test_no_history_is_unrated(): void
    {
        $r = $this->score([]);
        $this->assertNull($r['score']);
        $this->assertSame('unrated', $r['band']);
        $this->assertTrue($r['thin_file']);
    }

    public function test_thin_perfect_file_regresses_to_mean_not_top_grade(): void
    {
        // One flawless invoice must NOT mint an A — it regresses toward the neutral mean.
        $r = $this->score([
            'invoices_total' => 1, 'invoices_paid' => 1, 'total_billed' => 10000,
            'outstanding' => 0, 'on_time_rate' => 100, 'avg_days_late' => 0,
        ]);
        $this->assertTrue($r['thin_file']);
        $this->assertLessThan(70, $r['score']);      // pulled down from 100
        $this->assertGreaterThan(50, $r['score']);   // but not punished
        $this->assertNotSame('A', $r['grade']);
    }

    public function test_thick_excellent_payer_scores_top(): void
    {
        $r = $this->score([
            'invoices_total' => 12, 'invoices_paid' => 12, 'total_billed' => 120000,
            'outstanding' => 0, 'on_time_rate' => 100, 'avg_days_late' => 0,
        ]);
        $this->assertFalse($r['thin_file']);
        $this->assertSame('excellent', $r['band']);
        $this->assertSame('A', $r['grade']);
        $this->assertEqualsWithDelta(100, $r['score'], 0.01);
    }

    public function test_delinquent_thick_file_scores_low(): void
    {
        $r = $this->score([
            'invoices_total' => 12, 'invoices_paid' => 4, 'overdue_count' => 6,
            'total_billed' => 120000, 'outstanding' => 80000, 'on_time_rate' => 25, 'avg_days_late' => 45,
        ]);
        $this->assertLessThan(35, $r['score']);
        $this->assertSame('high_risk', $r['band']);
        $this->assertContains('6 invoice(s) currently overdue.', $r['factors']['notes']);
    }

    public function test_all_unpaid_scores_low_via_completion_and_arrears(): void
    {
        // No paid invoices → punctuality/lateness excluded; completion + arrears carry it low.
        $r = $this->score([
            'invoices_total' => 5, 'invoices_paid' => 0, 'overdue_count' => 5,
            'total_billed' => 50000, 'outstanding' => 50000,
        ]);
        $this->assertLessThan(35, $r['score']);
        $this->assertArrayNotHasKey('punctuality', $r['factors']['components']);
        $this->assertArrayHasKey('completion', $r['factors']['components']);
    }

    public function test_version_is_stamped(): void
    {
        $this->assertSame(TenantScoreService::VERSION, $this->score(['invoices_total' => 3, 'invoices_paid' => 3, 'on_time_rate' => 90, 'avg_days_late' => 2, 'total_billed' => 3000])['version']);
    }
}
