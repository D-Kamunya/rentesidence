<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Gate the owner surface behind acceptance of the CURRENT Terms & Conditions version. An owner
 * whose recorded acceptance doesn't match `terms_version` (never accepted, or a new version was
 * published) is redirected to the acceptance screen until they accept. Applied to the owner route
 * group; the acceptance routes themselves live OUTSIDE this gate (own group) so there's no loop.
 * Only owners are gated — the T&C is the owner/agency instrument.
 */
class EnsureTermsAccepted
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && (int) $user->role === (int) USER_ROLE_OWNER) {
            $current = (string) getOption('terms_version', '1.0');
            if ((string) $user->terms_accepted_version !== $current) {
                return redirect()->route('owner.terms.show');
            }
        }

        return $next($request);
    }
}
