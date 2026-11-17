<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Sends a new affiliate their one-time welcome as a REAL support conversation FROM the team — the
 * admin starts a thread (affiliate as requester, our warm message as the first reply), so their bell +
 * unread badge light like any admin reply and they can reply straight into it. Mirrors
 * OwnerWelcomeService. The welcome EMAIL already fires separately on registration, so the thread opts
 * OUT of the support email ($notifyByEmail = false) to avoid a double at signup. Idempotent via
 * affiliates.welcomed_at, so the registration hook and the backfill command both call it safely.
 */
class AffiliateWelcomeService
{
    /** Returns true if a welcome was sent this call, false if skipped (already welcomed / not an affiliate). */
    public static function send(User $user, bool $force = false): bool
    {
        $affiliate = Affiliate::where('user_id', $user->id)->first();
        if (! $affiliate || (! $force && $affiliate->welcomed_at)) {
            return false;
        }

        try {
            $appName = getOption('app_name') ?: 'Centresidence';
            $admin   = User::where('role', USER_ROLE_ADMIN)->first();
            if (! $admin) {
                return false;
            }

            $firstName = trim((string) ($user->first_name ?? '')) ?: __('there');

            $body = __(":name, welcome to the :app affiliate team — great to have you. 🙌\n\n"
                . "Here's the quickest path to your first earnings:\n"
                . "1. Finish the academy to get certified — it's short, and it unlocks everything.\n"
                . "2. Add your first landlord as a lead (your own, or one you know) — a warm, easy first win.\n"
                . "3. Help them get set up, and you start earning.\n\n"
                . "This is a direct line to our team — reply here any time you're stuck or want a hand, and we'll help you personally.",
                ['name' => $firstName, 'app' => $appName]
            );

            app(SupportTicketService::class)->adminStartConversation(
                $user,
                $admin,
                __('Welcome to the :app affiliate team 🎉', ['app' => $appName]),
                $body,
                false // the separate welcome EMAIL already covers email — don't double up at signup
            );

            $affiliate->forceFill(['welcomed_at' => now()])->save();
            return true;
        } catch (\Throwable $e) {
            Log::warning('Affiliate welcome conversation failed for user ' . $user->id . ': ' . $e->getMessage());
            return false;
        }
    }
}
