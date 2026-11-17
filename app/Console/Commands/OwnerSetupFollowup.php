<?php

namespace App\Console\Commands;

use App\Models\Owner;
use App\Services\OwnerSetupFollowupService;
use Illuminate\Console\Command;

/**
 * Drives the ongoing "stuck in setup" follow-up cadence — the automated second-and-onward touch after
 * the welcome. Scheduled to run regularly; for each welcomed owner the service decides whether a
 * follow-up is DUE (respecting the post-welcome grace, the spacing interval, and the per-step cap) and
 * which step to nudge about. Owners who finish setup, or aren't due yet, are skipped — so it's safe to
 * run on a cadence and it keeps catching owners who stall at each step.
 *
 *   php artisan owner:setup-followup
 *
 * --force sends a follow-up to every welcomed, still-stuck owner NOW, ignoring grace/spacing/cap — a
 * deliberate sweep of the current stuck cohort. Use it sparingly (each send posts into their thread).
 */
class OwnerSetupFollowup extends Command
{
    protected $signature = 'owner:setup-followup {--force : Send to every welcomed, still-stuck owner now, ignoring grace/spacing/cap} {--yes : Skip the confirmation prompt (for non-interactive shells)}';
    protected $description = 'Follow up welcomed owners stuck in setup, pointing each at their current next step. Bounded cadence.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        if ($force && ! $this->option('yes') && ! $this->confirm('This sends a follow-up to EVERY welcomed owner who is still stuck right now, ignoring the usual grace/spacing/cap, posting into their support thread. Continue?')) {
            $this->warn('Aborted.');
            return self::SUCCESS;
        }

        $sent = 0;
        // Iterate welcomed owners; the service applies completeness, grace, spacing and the per-step cap.
        Owner::whereNotNull('welcomed_at')->with('user')->chunkById(200, function ($owners) use (&$sent, $force) {
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
