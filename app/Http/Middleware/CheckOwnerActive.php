<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Owner;

class CheckOwnerActive
{
    /**
     * Handle an incoming request.
     * Redirects deactivated owners to the suspended page.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->role == USER_ROLE_OWNER) {
            $status = Owner::where('user_id', $user->id)->value('status');

            if ((int) $status !== 1) { // 1 = active, 0 = deactivated
                if (!$request->routeIs('owner.suspended') && !$request->routeIs('logout')) {
                    return redirect()->route('owner.suspended');
                }
            }
        }

        return $next($request);
    }
}