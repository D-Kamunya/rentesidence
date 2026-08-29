<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Forces an account that was issued a SYSTEM-generated password (a tenant created/imported by an
 * owner, or a credential re-send) to set their own password before using the app. The
 * `must_change_password` flag is cleared in ProfileController::changePasswordUpdate once they do.
 *
 * Runs in the `web` group: guests have no user so they pass straight through; only a
 * flagged, authenticated user is held on the change-password screen. The screen, its submit,
 * and logout are always allowed so there's no redirect loop and no lock-in.
 */
class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            $isAllowed = $request->routeIs('change-password', 'change-password.update')
                || $request->is('logout')
                || $request->is('*/logout');

            if (! $isAllowed) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => __('Please set your own password to continue.')], 423);
                }
                return redirect()->route('change-password')
                    ->with('info', __('Welcome! For your security, please set your own password to continue.'));
            }
        }

        return $next($request);
    }
}
