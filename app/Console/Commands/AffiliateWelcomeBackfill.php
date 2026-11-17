<?php

namespace App\Console\Commands;

use App\Models\Affiliate;
use App\Services\AffiliateWelcomeService;
use Illuminate\Console\Command;

/**
 * Sends the welcome support thread to existing affiliates who signed up before it existed.
 * Idempotent (AffiliateWelcomeService sets affiliates.welcomed_at), so it's safe to re-run — only
 * affiliates who haven't been welcomed get a message.
 *
 *   php artisan affiliate:welcome-backfill
 *
 * --force re-sends to EVERY affiliate (ignores welcomed_at) — a deliberate one-time migration onto
 * the thread welcome. Run --force ONCE: each forced send opens a fresh thread.
 */
class AffiliateWelcomeBackfill extends Command
{
    protected $signature = 'affiliate:welcome-backfill {--force : Re-send to EVERY affiliate, even those already welcomed (one-time use)}';
    protected $description = 'Send the welcome support thread to affiliates who have not received it yet.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        if ($force && ! $this->confirm('This re-sends the welcome to EVERY affiliate (even already-welcomed ones) and opens a fresh support thread each time. Run it only once. Continue?')) {
            $this->warn('Aborted.');
            return self::SUCCESS;
        }

        $sent = 0;
        $query = $force ? Affiliate::query() : Affiliate::whereNull('welcomed_at');

        $query->with('user')->chunkById(100, function ($affiliates) use (&$sent, $force) {
            foreach ($affiliates as $affiliate) {
                if ($affiliate->user && AffiliateWelcomeService::send($affiliate->user, $force)) {
                    $sent++;
                }
            }
        });

        $this->info("Welcome sent to {$sent} affiliate(s).");
        return self::SUCCESS;
    }
}
