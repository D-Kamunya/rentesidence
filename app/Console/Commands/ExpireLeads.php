<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireLeads extends Command
{
    protected $signature   = 'leads:expire {--dry-run : Preview what would expire without making changes}';
    protected $description = 'Expire leads whose ownership window has closed and queue marketplace leads for recycling';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be written.');
        }

        /*
         * Fetch candidates efficiently at the DB level:
         * - Not already expired/closed/protected
         * - ownership_expires_at is in the past
         *
         * We still call shouldExpire() on each model as the final guard,
         * but the query trims the working set to only realistic candidates.
         */
        $candidates = Lead::query()
            ->whereNotNull('ownership_expires_at')
            ->where('ownership_expires_at', '<', now())
            ->whereNotIn('status', array_merge(
                Lead::CLOSED_STATUSES,
                Lead::PROTECTED_STATUSES,
                ['expired']
            ))
            ->get();

        $this->info("Candidates found: {$candidates->count()}");

        $expired   = 0;
        $recycled  = 0;
        $skipped   = 0;

        foreach ($candidates as $lead) {

            // Final model-level guard (catches any edge cases the query missed)
            if (! $lead->shouldExpire()) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("  [dry-run] Would expire lead #{$lead->id} (source: {$lead->source}, status: {$lead->status})");
                $expired++;
                continue;
            }

            DB::transaction(function () use ($lead, &$expired, &$recycled) {
                $lead->expire();

                LeadActivity::create([
                    'lead_id'    => $lead->id,
                    'type'       => 'lead_expired',
                    'description'      => 'Ownership window closed — lead expired.',
                ]);

                $expired++;

                // Queue marketplace leads for recycling
                if ($lead->shouldReturnToMarketplace()) {
                    $lead->update([
                        'affiliate_id'       => null,
                        'marketplace_status' => 'marketplace',
                        'claimed_at'         => null,
                        'marketplace_at'     => now(),
                    ]);

                    LeadActivity::create([
                        'lead_id'    => $lead->id,
                        'type'       => 'recycled_to_marketplace',
                        'description'      => 'Lead returned to marketplace after ownership expiry.',
                    ]);

                    $recycled++;
                }
            });
        }

        $this->info("Expired:  {$expired}");
        $this->info("Recycled: {$recycled}");
        $this->info("Skipped:  {$skipped}");

        Log::info('leads:expire completed', compact('expired', 'recycled', 'skipped'));

        return self::SUCCESS;
    }
}