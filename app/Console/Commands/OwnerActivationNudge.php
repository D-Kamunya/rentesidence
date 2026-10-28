<?php

namespace App\Console\Commands;

use App\Jobs\Mail\SendOwnerActivationNudgeMail;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Console\Command;

/**
 * Scheduled: nudge OWNERS who signed up but haven't activated — the funnel is signup → property →
 * tenant → rent, and an owner stuck before their first tenant earns nothing (for them, us, or the
 * affiliate who brought them). Two stages off one counter: 'property' (no property yet → add one)
 * and 'tenant' (has a property but no tenant → add a tenant and start billing). Bounded so it never
 * pesters: a grace period after signup, a cap, spacing, and it STOPS the moment they add a tenant.
 */
class OwnerActivationNudge extends Command
{
    protected $signature = 'owner:activation-nudge';

    protected $description = 'Nudge owners who signed up but haven\'t added a property/tenant yet. Bounded.';

    private const GRACE_DAYS   = 3; // wait this long after signup before the first nudge
    private const SPACING_DAYS = 7; // minimum gap between nudges
    private const MAX_NUDGES   = 2; // never more than this

    public function handle(): int
    {
        $sent    = 0;
        $grace   = now()->subDays(self::GRACE_DAYS);
        $spacing = now()->subDays(self::SPACING_DAYS);

        Owner::with('user')
            ->where('status', 1)                                   // active owners only
            ->where('activation_nudge_count', '<', self::MAX_NUDGES)
            ->where(function ($q) use ($spacing) {
                $q->whereNull('activation_nudged_at')->orWhere('activation_nudged_at', '<=', $spacing);
            })
            ->chunkById(200, function ($owners) use (&$sent, $grace) {
                foreach ($owners as $owner) {
                    if (! $owner->user || ! $owner->user->email) {
                        continue;
                    }
                    // Only after a grace period since signup — give them room to get started on their own.
                    if (! $owner->created_at || $owner->created_at > $grace) {
                        continue;
                    }

                    $uid = $owner->user_id;

                    // A tenant means they're substantially activated (they can bill + collect) → stop.
                    if (Tenant::where('owner_user_id', $uid)->exists()) {
                        continue;
                    }

                    $stage = Property::where('owner_user_id', $uid)->exists() ? 'tenant' : 'property';

                    SendOwnerActivationNudgeMail::dispatch($uid, $stage);
                    $owner->increment('activation_nudge_count');
                    $owner->update(['activation_nudged_at' => now()]);
                    $sent++;
                }
            });

        $this->info("Owner activation nudge: {$sent} sent.");

        return self::SUCCESS;
    }
}
