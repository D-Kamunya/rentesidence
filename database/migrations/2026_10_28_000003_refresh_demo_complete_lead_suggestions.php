<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One-time cleanup of already-stored lead suggestions after the free-first engine change.
 *
 * Suggestions are PERSISTED rows whose message text is frozen at generation, so rows created
 * before the engine switched to "set them up on Free" still read "request trial", and a lead that
 * was both demo_completed AND had a demo_viewed activity got TWO near-identical Call cards (the two
 * branches both fired — now gated so new ones won't, but existing rows linger). This refreshes them:
 *   1. Rewrite the stale post-demo copy in place ("...and request trial" → "...and set them up on Free").
 *   2. Expire the redundant "watched your demo" nudge on leads already marked demo_completed (the
 *      demo_completed branch already covers them).
 * Pending rows only — completed/dismissed/expired history is left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Refresh stale free-first copy on pending suggestions.
        DB::table('lead_suggestions')
            ->where('status', 'pending')
            ->where('message', 'like', '%and request trial%')
            ->update(['message' => DB::raw("REPLACE(message, 'and request trial', 'and set them up on Free')")]);

        // 2. Expire the now-redundant demo_viewed nudge on leads already at demo_completed.
        DB::table('lead_suggestions')
            ->where('status', 'pending')
            ->where('message', 'like', '%watched your demo%')
            ->whereIn('lead_id', function ($q) {
                $q->select('id')->from('leads')->where('status', 'demo_completed');
            })
            ->update(['status' => 'expired']);
    }

    public function down(): void
    {
        // One-time data cleanup — not reversible.
    }
};
