<?php

namespace App\Http\Middleware;

use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Restricts access to the Finance Partner portal (role 6).
 */
class FinancePartner
{
    use ResponseTrait;

    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->role != USER_ROLE_FINANCE_PARTNER) {
            if ($request->wantsJson()) {
                return $this->error([], __('Unauthorized'));
            }
            abort(403);
        }

        return $next($request);
    }
}
