<?php

namespace App\Console\Commands;

use App\Services\Sms\SmsCreditsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Recurring re-engagement digest for owners sitting OUT of SMS credits with a growing backlog of
 * messages that couldn't be sent. Fills the gap between the one-time zero-credit notice (fires once
 * at the crossing) and the per-batch summary (fires only when a batch happens to run): neither
 * re-surfaces the backlog for an owner who's been stuck for days. Helpful nudge / silent marketing
 * — "you have N messages paused over X days, top up to resume."
 *
 * Email + in-app only (they have no SMS credit; it's a platform→owner nudge). Sends ONLY to owners
 * who are STILL out of credit (topped-up owners are skipped) and is throttled per owner via Cache
 * so re-runs / a tighter schedule never nag. Scheduled weekly.
 */
class SmsPausedDigest extends Command
{
    protected $signature = 'sms:paused-digest {--days=} {--force : ignore the per-owner throttle}';
    protected $description = 'Nudge owners who are out of SMS credits with a paused-message backlog to top up.';

    public function handle(): int
    {
        $days        = (int) ($this->option('days') ?: getOption('sms_paused_digest_days', 7));
        $throttleDays = (int) getOption('sms_paused_digest_throttle_days', 7);
        $force       = (bool) $this->option('force');

        $backlogs = SmsCreditsService::ownersWithPausedBacklog($days);
        $sent = 0;

        foreach ($backlogs as $ownerUserId => $count) {
            $ownerUserId = (int) $ownerUserId;

            // Skip owners who have since topped up — no longer stuck, nothing to nudge.
            if (SmsCreditsService::balance($ownerUserId) >= 1) {
                continue;
            }

            $cacheKey = 'sms-paused-digest:' . $ownerUserId;
            if (! $force && Cache::has($cacheKey)) {
                continue; // throttled — already nudged this owner within the window
            }

            try {
                SmsCreditsService::notifyPausedBacklog($ownerUserId, (int) $count, $days);
                Cache::put($cacheKey, now()->toDateTimeString(), now()->addDays(max(1, $throttleDays)));
                $sent++;
            } catch (\Throwable $e) {
                Log::error('SmsPausedDigest failed', ['owner_user_id' => $ownerUserId, 'error' => $e->getMessage()]);
            }
        }

        $this->info("SMS paused-digest: notified {$sent} owner(s) with a backlog in the last {$days} days.");

        return self::SUCCESS;
    }
}
