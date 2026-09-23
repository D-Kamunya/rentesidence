<?php

namespace App\Services\Screening;

/**
 * The compound rental SCORE model — turns a tenant's objective payment-behaviour metrics into a
 * transparent 0–100 score + band. Behaviour-weighted (how they actually paid), explainable
 * (returns the factor breakdown), and versioned so the model can evolve without silently
 * rewriting history. Thin files regress to a neutral mean so a one-invoice tenant is never
 * over-branded either way.
 *
 * This is deliberately a pure function of the metrics (no DB) so it's easy to test and reason
 * about, and so the same model can score a live profile or a what-if.
 */
class TenantScoreService
{
    public const VERSION = 'v1';

    /** Component weights (renormalised over whichever components are available). */
    private const WEIGHTS = [
        'punctuality' => 0.40, // on-time payment rate — the strongest reliability signal
        'completion'  => 0.25, // do they actually clear their invoices at all
        'arrears'     => 0.20, // current outstanding vs billed (delinquency now)
        'lateness'    => 0.15, // how late when late (severity)
        'reputation'  => 0.12, // SECONDARY: aggregated landlord ratings (only when present)
    ];

    private const NEUTRAL = 55.0;          // mean a thin file regresses toward
    private const FULL_CONFIDENCE_AT = 8;  // invoices for full confidence (~8 months of rent)

    /**
     * @param array $m keys: on_time_rate, avg_days_late, invoices_total, invoices_paid,
     *                 overdue_count, total_billed, outstanding
     * @return array{score:?float, band:string, grade:string, version:string, thin_file:bool, factors:array}
     */
    public function score(array $m): array
    {
        $total    = (int) ($m['invoices_total'] ?? 0);
        $paid     = (int) ($m['invoices_paid'] ?? 0);
        $billed   = (float) ($m['total_billed'] ?? 0);
        $out      = (float) ($m['outstanding'] ?? 0);
        $overdue  = (int) ($m['overdue_count'] ?? 0);
        $onTime   = $m['on_time_rate'] ?? null;   // 0–100 or null
        $avgLate  = $m['avg_days_late'] ?? null;  // days or null

        // No invoices at all → nothing to score yet.
        if ($total === 0) {
            return [
                'score' => null, 'band' => 'unrated', 'grade' => '—', 'version' => self::VERSION,
                'thin_file' => true, 'factors' => ['notes' => ['No payment history yet.']],
            ];
        }

        $components = [];

        // Punctuality — % of paid invoices that were on time.
        if ($paid > 0 && $onTime !== null) {
            $components['punctuality'] = $this->clamp((float) $onTime);
        }
        // Completion — share of all invoices actually paid.
        $components['completion'] = $this->clamp($total > 0 ? ($paid / $total) * 100 : 0);
        // Arrears — how much is currently outstanding relative to everything billed.
        $arrearsRatio = $billed > 0 ? min(1, $out / $billed) : ($out > 0 ? 1 : 0);
        $arrears = 100 * (1 - $arrearsRatio) - min(20, $overdue * 5); // small extra hit per overdue bill
        $components['arrears'] = $this->clamp($arrears);
        // Lateness severity — only meaningful once something's been paid.
        if ($paid > 0 && $avgLate !== null) {
            $components['lateness'] = $this->clamp(100 - ((float) $avgLate) * 2.5); // ~40 days late → 0
        }
        // Reputation — aggregated landlord ratings (secondary; only when the person has been rated).
        if (($m['ratings_count'] ?? 0) > 0 && ($m['landlord_rating'] ?? null) !== null) {
            $components['reputation'] = $this->clamp((float) $m['landlord_rating']);
        }

        // Weighted composite over available components (renormalise weights).
        $wSum = 0; $acc = 0;
        foreach ($components as $key => $val) {
            $w = self::WEIGHTS[$key];
            $acc += $val * $w;
            $wSum += $w;
        }
        $composite = $wSum > 0 ? $acc / $wSum : self::NEUTRAL;

        // Thin-file regression to the mean — confidence grows with invoice count.
        $confidence = min(1.0, $total / self::FULL_CONFIDENCE_AT);
        $final = round(self::NEUTRAL * (1 - $confidence) + $composite * $confidence, 2);
        $thin  = $total < 3 || $confidence < 0.5;

        [$band, $grade] = $this->band($final);

        return [
            'score'     => $final,
            'band'      => $band,
            'grade'     => $grade,
            'version'   => self::VERSION,
            'thin_file' => $thin,
            'factors'   => [
                'components'    => array_map(fn ($v) => round($v, 1), $components),
                'weights'       => array_intersect_key(self::WEIGHTS, $components),
                'raw_composite' => round($composite, 2),
                'confidence'    => round($confidence, 2),
                'notes'         => $this->notes($components, $thin, $overdue),
            ],
        ];
    }

    private function band(float $s): array
    {
        return match (true) {
            $s >= 80 => ['excellent', 'A'],
            $s >= 65 => ['good', 'B'],
            $s >= 50 => ['fair', 'C'],
            $s >= 35 => ['poor', 'D'],
            default  => ['high_risk', 'E'],
        };
    }

    private function notes(array $c, bool $thin, int $overdue): array
    {
        $n = [];
        if ($thin) {
            $n[] = 'Limited history — score is provisional and will firm up with more tenancy data.';
        }
        if (isset($c['punctuality'])) {
            $n[] = $c['punctuality'] >= 85 ? 'Consistently pays on time.'
                : ($c['punctuality'] < 50 ? 'Frequently pays late.' : 'Mixed punctuality.');
        }
        if ($overdue > 0) {
            $n[] = "{$overdue} invoice(s) currently overdue.";
        }
        if (isset($c['completion']) && $c['completion'] < 70) {
            $n[] = 'Some invoices left unpaid.';
        }
        return $n;
    }

    private function clamp(float $v): float
    {
        return max(0, min(100, $v));
    }
}
