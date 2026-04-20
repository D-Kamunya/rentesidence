<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AcademyModule;

class EnsureAcademyCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    public function handle($request, Closure $next)
    {
        $affiliate = auth()->user()->affiliate;

        // If no modules exist at all, let them through
        if (AcademyModule::count() === 0) {
            return $next($request);
        }

        // Otherwise enforce the academy completion requirement
        if ($affiliate && $affiliate->academy_status !== 'completed') {
            return redirect()->route('affiliate.academy.index');
        }

        return $next($request);
    }
}
