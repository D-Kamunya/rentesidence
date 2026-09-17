<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Guards owner-bound tenant routes (marketplace, orders, tickets, information, maintenance,
 * documents, agreement, utilities). An ownerless "Tenant Helper" tenant — one whose tenancy is
 * closed — has no owner to serve these, and they're hidden from the nav; this stops the routes
 * being reached directly (which would 500 on missing owner data or leak the former owner's data).
 */
class BlockOwnerlessTenant
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && method_exists($user, 'isOwnerlessTenant') && $user->isOwnerlessTenant()) {
            $msg = __('That section is only available while you have an active tenancy.');
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => $msg], 403);
            }
            return redirect()->route('tenant.dashboard')->with('error', $msg);
        }
        return $next($request);
    }
}
