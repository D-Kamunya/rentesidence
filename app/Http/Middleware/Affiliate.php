<?php

namespace App\Http\Middleware;

use App\Models\Affiliate as AffiliateModel;
use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Affiliate
{
    use ResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->role != USER_ROLE_AFFILIATE) {
            if ($request->wantsJson()) {
                $message = __("Unauthorized");
                return $this->error([], $message);
            } else {
                abort('403');
            }
        }

        // Suspended affiliates keep an account but lose all access until reinstated.
        $status = AffiliateModel::where('user_id', Auth::id())->value('status');
        if ((int) $status !== AFFILIATE_STATUS_ACTIVE) {
            if ($request->wantsJson()) {
                return $this->error([], __('Your affiliate account is suspended. Please contact the administrator.'));
            }
            if (!$request->routeIs('affiliate.suspended') && !$request->routeIs('logout')) {
                return redirect()->route('affiliate.suspended');
            }
        }

        return $next($request);
    }
}
