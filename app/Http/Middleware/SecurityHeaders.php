<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds the standard defence-in-depth security response headers app-wide. Cheap,
 * additive, and expected by any security-conscious org (bank onboarding). Deliberately
 * omits Content-Security-Policy — the app relies heavily on inline scripts/styles, so a
 * real CSP needs its own careful pass (tracked separately) rather than a header that
 * would silently break pages.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Don't rewrite headers on streamed/binary downloads that manage their own.
        $headers = $response->headers;

        // MIME-sniffing: force the declared Content-Type (stops a browser executing an
        // uploaded "image" as script).
        $headers->set('X-Content-Type-Options', 'nosniff', false);

        // Clickjacking: only THIS origin may frame the app.
        $headers->set('X-Frame-Options', 'SAMEORIGIN', false);

        // Referrer: never leak a full URL (incl. one-time pay tokens in the path) to a
        // third-party origin — send only the origin cross-site, the full path same-site.
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);

        // Turn off powerful features the app doesn't use, so an injected script can't
        // reach for them.
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()', false);

        // HSTS: only over HTTPS (never poison a plain-HTTP local dev origin). One year,
        // no includeSubDomains/preload — a safe default that doesn't over-commit
        // subdomains that may not yet serve HTTPS.
        if ($request->secure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000', false);
        }

        return $response;
    }
}
