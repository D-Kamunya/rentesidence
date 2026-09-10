<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Gate the owner surface behind acceptance of the CURRENT Terms & Conditions version. An owner
 * whose recorded acceptance doesn't match `terms_version` (never accepted, or a new version was
 * published) is redirected to the acceptance screen until they accept. Applied to the owner route
 * group; the acceptance routes themselves live OUTSIDE this gate (own group) so there's no loop.
 * Only owners are gated — the T&C is the owner/agency instrument.
 *
 * Degrades gracefully: if the acceptance columns aren't migrated yet (shared-host deploy lag), the
 * gate is a no-op so login is never broken — it activates automatically once the migration runs.
 */
class EnsureTermsAccepted
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && (int) $user->role === (int) USER_ROLE_OWNER && self::columnsReady()) {
            $current = (string) getOption('terms_version', '1.0');
            if ((string) $user->terms_accepted_version !== $current) {
                return redirect()->route('owner.terms.show');
            }
        }

        return $next($request);
    }

    /** Are the acceptance columns present? The gate self-activates once the migration has run. */
    public static function columnsReady(): bool
    {
        return Schema::hasColumn('users', 'terms_accepted_version');
    }
}
