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
        $grace   = now()->subDays(self::GRACE_DAYS);
        $spacing = now()->subDays(self::SPACING_DAYS);

        // One bounded engine, two stages: 'academy' (signed up, not certified → finish training) and
        // 'lead' (certified, no leads → add your landlord). One counter caps total nudges either way.
        Affiliate::with('user')
            ->where('status', 1)                                  // active affiliates only
            ->where('activation_nudge_count', '<', self::MAX_NUDGES)
            ->where(function ($q) use ($spacing) {
                $q->whereNull('activation_nudged_at')->orWhere('activation_nudged_at', '<=', $spacing);
            })
            ->chunkById(200, function ($affiliates) use (&$sent, $grace) {
                foreach ($affiliates as $affiliate) {
                    if (! $affiliate->user) {
                        continue;
                    }

                    $stage = null;
                    if ($affiliate->academy_status !== 'completed') {
                        // Not certified yet → nudge to finish the academy (after a grace since signup).
                        if ($affiliate->created_at && $affiliate->created_at <= $grace) {
                            $stage = 'academy';
                        }
                    } else {
                        // Certified → nudge to add a first lead, but stop the moment they've added one.
                        if (Lead::where('affiliate_id', $affiliate->user_id)->exists()) {
                            continue;
                        }
                        if ($affiliate->academy_certified_at && $affiliate->academy_certified_at <= $grace) {
                            $stage = 'lead';
                        }
                    }

                    if (! $stage) {
                        continue;
                    }

                    SendAffiliateActivationNudgeMail::dispatch($affiliate->id, $stage);
                    $affiliate->increment('activation_nudge_count');
                    $affiliate->update(['activation_nudged_at' => now()]);
                    $sent++;
                }
            });

        $this->info("Affiliate activation nudge: {$sent} sent.");

        return self::SUCCESS;
    }
}
