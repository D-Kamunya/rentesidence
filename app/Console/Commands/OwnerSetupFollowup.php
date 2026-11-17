<?php

namespace App\Console\Commands;

use App\Models\Owner;
use App\Services\OwnerSetupFollowupService;
use Illuminate\Console\Command;

/**
 * Sends the one-time "stuck in setup" follow-up to welcomed owners who haven't finished setting up
 * to collect rent (a second touch after the welcome). Idempotent via owners.setup_followup_at, so
 * it's safe to re-run — only owners who haven't had the follow-up AND aren't fully set up get one.
 *
 *   php artisan owner:setup-followup
 *
 * Scheduled to run on a cadence (grace handled by only targeting owners welcomed a while ago is not
 * needed — the welcome itself is the first touch). Use --force to re-send to EVERY welcomed, still-
 * stuck owner even if they've already had a follow-up — a deliberate one-time nudge of the current
 * cohort. Run --force ONCE (each send posts another message into their support thread).
 */
class OwnerSetupFollowup extends Command
{
    protected $signature = 'owner:setup-followup {--force : Re-send to every welcomed, still-stuck owner even if already followed up (one-time use)}';
    protected $description = 'Follow up welcomed owners who are stuck in setup, pointing them at their next step.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        if ($force && ! $this->confirm('This re-sends the setup follow-up to EVERY welcomed owner who is still stuck, even those already followed up, posting another message into their support thread. Run it only once. Continue?')) {
            $this->warn('Aborted.');
            return self::SUCCESS;
        }

        $sent  = 0;
        $query = Owner::whereNotNull('welcomed_at');
        if (! $force) {
            $query->whereNull('setup_followup_at');
        }

        $query->with('user')->chunkById(200, function ($owners) use (&$sent, $force) {
            foreach ($owners as $owner) {
                if ($owner->user && OwnerSetupFollowupService::send($owner->user, $force)) {
                    $sent++;
                }
            }
        });

        $this->info("Setup follow-up sent to {$sent} stuck owner(s).");
        return self::SUCCESS;
    }
}
