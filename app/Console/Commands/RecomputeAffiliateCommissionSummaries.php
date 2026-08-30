<?php

namespace App\Console\Commands;

use App\Models\AffiliateCommission;
use App\Services\AffiliateCommissionService;
use Illuminate\Console\Command;

/**
 * Reconcile every affiliate commission period-summary (affiliate_commission_payments) against
 * the raw affiliate_commissions source of truth. New commissions already keep the summary fresh
 * via AffiliateCommissionService::recalculatePeriodSummary on write, so this is for one-off
 * reconciliation — e.g. after the payout formula changed, or to heal any summary that drifted
 * because a commission was created outside the normal path. Idempotent and safe to re-run.
 */
class RecomputeAffiliateCommissionSummaries extends Command
{
    protected $signature = 'affiliate:recompute-summaries {--affiliate= : Limit to one affiliate id}';

    protected $description = 'Rebuild affiliate commission period summaries from the raw commissions (idempotent).';

    public function handle(AffiliateCommissionService $service): int
    {
        $periods = AffiliateCommission::query()
            ->when($this->option('affiliate'), fn ($q, $id) => $q->where('affiliate_id', $id))
            ->selectRaw('affiliate_id, period_year, period_month')
            ->whereNotNull('period_month')
            ->whereNotNull('period_year')
            ->distinct()
            ->get();

        if ($periods->isEmpty()) {
            $this->info('No affiliate commission periods to reconcile.');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($periods as $p) {
            try {
                $service->recalculatePeriodSummary((int) $p->affiliate_id, (int) $p->period_month, (int) $p->period_year);
                $count++;
            } catch (\Throwable $e) {
                $this->error("Failed affiliate {$p->affiliate_id} {$p->period_year}-{$p->period_month}: {$e->getMessage()}");
            }
        }

        $this->info("Reconciled {$count} affiliate commission period summaries.");
        return self::SUCCESS;
    }
}
