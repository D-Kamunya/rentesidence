<?php

namespace App\Http\Middleware;

use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Owner
{
    use ResponseTrait;

    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->role != USER_ROLE_OWNER) {
            if ($request->wantsJson()) {
                $message = __("Unauthorized");
                return $this->error([], $message);
            } else {
                abort('403');
            }
        }
        return $next($request);
    }
}