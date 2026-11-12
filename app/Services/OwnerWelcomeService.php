<?php

namespace App\Services;

use App\Models\Owner;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Sends a new owner their one-time welcome as a REAL support conversation FROM the team — the admin
 * starts a thread (owner as requester, our warm message as the first reply), so the owner's bell +
 * unread badge light exactly like any admin reply, and they can reply straight into it to reach us.
 * It's a notification, not a blocking modal, so it never gets in the way of the setup checklist.
 * Idempotent via owners.welcomed_at, so the signup hook and the backfill command both call it safely.
 * In-app only — the (CS) welcome EMAIL is sent separately.
 */
class OwnerWelcomeService
{
    /** Returns true if a welcome was sent this call, false if skipped (already welcomed / not an owner). */
    public static function send(User $user): bool
    {
        $owner = Owner::where('user_id', $user->id)->first();
        if (! $owner || $owner->welcomed_at) {
            return false;
        }

        try {
            $appName = getOption('app_name') ?: 'Centresidence';
            // The thread needs a real admin author (the bell inner-joins users on sender_id). If no
            // admin exists yet (bare install), skip gracefully rather than send a senderless message.
            $admin = User::where('role', USER_ROLE_ADMIN)->first();
            if (! $admin) {
                return false;
            }

            $firstName = trim((string) ($user->first_name ?? '')) ?: __('there');

            $body = __(":name, welcome to :app — we're really glad you're here. 👋\n\n"
                . "Your account is ready. Two quick things to start collecting rent:\n"
                . "1. Add your first property and a tenant from your dashboard checklist.\n"
                . "2. Set up how you get paid — add your M-Pesa Till or Paybill under Settings → Gateway, so rent comes straight to you.\n\n"
                . "This is a direct line to our team — just reply here any time you're stuck or have a question, and we'll help you personally.",
                ['name' => $firstName, 'app' => $appName]
            );

            app(SupportTicketService::class)->adminStartConversation(
                $user,
                $admin,
                __('Welcome to :app 🎉', ['app' => $appName]),
                $body
            );

            $owner->forceFill(['welcomed_at' => now()])->save();
            return true;
        } catch (\Throwable $e) {
            Log::warning('Owner welcome conversation failed for user ' . $user->id . ': ' . $e->getMessage());
            return false;
        }
    }
}
