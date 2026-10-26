<?php

namespace App\Console\Commands;

use App\Jobs\Mail\SendAffiliateActivationNudgeMail;
use App\Models\Affiliate;
use App\Models\Lead;
use Illuminate\Console\Command;

/**
 * Scheduled: nudge CERTIFIED affiliates who still have ZERO leads to add their first (their own
 * landlord — a guaranteed warm lead). Closes the "sign up → finish academy → sit idle" churn gap.
 * Bounded so it never pesters: fires only after a grace period post-certification, caps at MAX
 * nudges, spaces them out, and STOPS the moment the affiliate adds any lead.
 */
class AffiliateActivationNudge extends Command
{
    protected $signature = 'affiliate:activation-nudge';

    protected $description = 'Nudge certified affiliates with no leads to add their first (their own landlord). Bounded.';

    private const GRACE_DAYS   = 2; // wait this long after certification before the first nudge
    private const SPACING_DAYS = 5; // minimum gap between nudges
    private const MAX_NUDGES   = 2; // never more than this

    public function handle(): int
    {
        $sent = 0;

        Affiliate::with('user')
            ->where('status', 1)                                  // active affiliates only
            ->where('academy_status', 'completed')
            ->whereNotNull('academy_certified_at')
            ->where('academy_certified_at', '<=', now()->subDays(self::GRACE_DAYS))
            ->where('activation_nudge_count', '<', self::MAX_NUDGES)
            ->where(function ($q) {
                $q->whereNull('activation_nudged_at')
                  ->orWhere('activation_nudged_at', '<=', now()->subDays(self::SPACING_DAYS));
            })
            ->chunkById(200, function ($affiliates) use (&$sent) {
                foreach ($affiliates as $affiliate) {
                    if (! $affiliate->user) {
                        continue;
                    }
                    // Stop the moment they've taken action — a single lead means they're off the mark.
                    if (Lead::where('affiliate_id', $affiliate->user_id)->exists()) {
                        continue;
                    }

                    SendAffiliateActivationNudgeMail::dispatch($affiliate->id);
                    $affiliate->increment('activation_nudge_count');
                    $affiliate->update(['activation_nudged_at' => now()]);
                    $sent++;
                }
            });

        $this->info("Affiliate activation nudge: {$sent} sent.");

        return self::SUCCESS;
    }
}
