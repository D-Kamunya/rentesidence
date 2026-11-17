<?php

namespace App\Console\Commands;

use App\Models\Affiliate;
use App\Services\AffiliateEngagementService;
use Illuminate\Console\Command;

/**
 * Drives the affiliate engagement cadence — the automated follow-up that re-engages idle affiliates
 * through their current sticking point (academy → first lead → convert) until they land a real win.
 * Scheduled regularly; the service decides whether a follow-up is DUE (grace / spacing / per-stage cap)
 * and which stage to nudge. Affiliates who are active, or aren't due, are skipped — safe to run on a
 * cadence and it keeps catching affiliates who stall at each stage.
 *
 *   php artisan affiliate:engagement-followup
 *
 * --force sends to every welcomed, still-stuck affiliate NOW, ignoring grace/spacing/cap — a deliberate
 * sweep of the current idle cohort. Use sparingly (each send posts into their thread).
 */
class AffiliateEngagementFollowup extends Command
{
    protected $signature = 'affiliate:engagement-followup {--force : Send to every welcomed, still-stuck affiliate now, ignoring grace/spacing/cap}';
    protected $description = 'Follow up welcomed affiliates stuck before their first win, pointing each at their current next step.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        if ($force && ! $this->confirm('This sends a follow-up to EVERY welcomed affiliate who is still stuck right now, ignoring the usual grace/spacing/cap, posting into their support thread. Continue?')) {
            $this->warn('Aborted.');
            return self::SUCCESS;
        }

        $sent = 0;
        Affiliate::whereNotNull('welcomed_at')->with('user')->chunkById(200, function ($affiliates) use (&$sent, $force) {
            foreach ($affiliates as $affiliate) {
                if ($affiliate->user && AffiliateEngagementService::send($affiliate->user, $force)) {
                    $sent++;
                }
            }
        });

        $this->info("Engagement follow-up sent to {$sent} stuck affiliate(s).");
        return self::SUCCESS;
    }
}
