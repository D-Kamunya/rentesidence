<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Captures an affiliate's `?referral=CODE` on any landing and remembers it (session + a 60-day
 * cookie) so it survives navigation and a later return — then the contact form attributes the
 * enquiry to that affiliate. This is the first half of the attribution loop; without it the
 * shareable referral link leaks before the finish line.
 */
class CaptureAffiliateReferral
{
    public const KEY = 'cs_referral';

    public function handle(Request $request, Closure $next)
    {
        $ref = $request->query('referral');

        // Referral codes are strtoupper(Str::random(12)) — plain alphanumerics. Validate loosely to
        // avoid persisting junk, and never overwrite an existing capture with an empty value.
        if (is_string($ref) && preg_match('/^[A-Za-z0-9]{4,32}$/', $ref)) {
            $request->session()->put(self::KEY, $ref);
            Cookie::queue(self::KEY, $ref, 60 * 24 * 60); // 60 days, in minutes
        }

        return $next($request);
    }
}
