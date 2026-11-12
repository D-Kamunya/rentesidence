<?php

namespace App\Console\Commands;

use App\Models\Owner;
use App\Services\OwnerWelcomeService;
use Illuminate\Console\Command;

/**
 * Sends the in-app welcome message to existing owners who signed up before it existed.
 * Idempotent (OwnerWelcomeService sets owners.welcomed_at), so it's safe to re-run — only
 * owners who haven't been welcomed get a message.
 *
 *   php artisan owner:welcome-backfill
 *
 * The welcome fires automatically at signup, so once everyone's been welcomed this reports 0.
 * Use --force for a DELIBERATE one-time re-welcome of EVERY owner (ignores welcomed_at) — e.g.
 * to migrate the already-onboarded cohort onto the new support-thread welcome. Run --force ONCE:
 * each forced send opens a fresh thread, so re-running it would send duplicates.
 *
 *   php artisan owner:welcome-backfill --force
 */
class OwnerWelcomeBackfill extends Command
{
    protected $signature = 'owner:welcome-backfill {--force : Re-send to EVERY owner, even those already welcomed (one-time use)}';
    protected $description = 'Send the in-app welcome message to owners who have not received it yet.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        if ($force && ! $this->confirm('This re-sends the welcome to EVERY owner (even already-welcomed ones) and opens a fresh support thread each time. Run it only once. Continue?')) {
            $this->warn('Aborted.');
            return self::SUCCESS;
        }

        $sent = 0;
        $query = $force ? Owner::query() : Owner::whereNull('welcomed_at');

        $query->with('user')->chunkById(100, function ($owners) use (&$sent, $force) {
            foreach ($owners as $owner) {
                if ($owner->user && OwnerWelcomeService::send($owner->user, $force)) {
                    $sent++;
                }
            }
        });

        $this->info("Welcome message sent to {$sent} owner(s).");
        return self::SUCCESS;
    }
}
