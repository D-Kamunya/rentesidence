<?php

namespace App\Services;

use App\Models\Owner;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/**
 * Sends a new owner their one-time in-app welcome (a bell notification pointing them to their
 * first step + Support). Idempotent via owners.welcomed_at, so the signup hook and the backfill
 * command can both call it safely. In-app only — the (CS) welcome EMAIL is sent separately.
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
            // A real sender — the bell inner-joins users on sender_id, so a null sender never shows.
            $sender = User::where('role', USER_ROLE_ADMIN)->value('id') ?? $user->id;
            $url = Route::has('owner.dashboard') ? route('owner.dashboard') : url('/');

            addNotification(
                __('Welcome to :app 🎉', ['app' => $appName]),
                __('You\'re all set. Add your first property to start collecting rent on M-Pesa — and if you ever get stuck, reach us anytime from Support. We\'re here to help.'),
                $url,
                null,
                (int) $user->id,
                (int) $sender
            );

            $owner->forceFill(['welcomed_at' => now()])->save();
            return true;
        } catch (\Throwable $e) {
            Log::warning('Owner welcome notification failed for user ' . $user->id . ': ' . $e->getMessage());
            return false;
        }
    }
}
