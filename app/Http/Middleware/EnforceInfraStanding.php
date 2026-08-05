<?php

namespace App\Http\Middleware;

use App\Centresidence\Services\OwnerBillingStandingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Readonly/degraded gate for owners whose module-infra bill is OVERDUE.
 *
 * Enforcement for the merged plan+infra model: an owner can't skip the infra
 * portion. When infra is overdue they keep FULL READ access (and the pay flow),
 * but the money-making / expansion writes are blocked until they settle —
 * adding properties/units/tenants, raising invoices, applying for new financing,
 * listing products. Operational, tenant-facing writes (tickets, maintenance) and
 * anything that PAYS us (facility repayments, the bill itself) stay open, so a
 * delinquent owner never harms their tenants and always has a way to clear it.
 *
 * Only GATES the named actions below on non-safe methods; everything else passes.
 */
class EnforceInfraStanding
{
    /** Route-name fragments for money-making / expansion actions (blocked while overdue). */
    private array $gated = [
        '.invoice.store', '.invoice.update', '.invoice.recurring-setting.store',
        '.property.store', '.property-information.store', '.unit.store',
        '.tenant.store', '.tenant.applications.store', '.tenant.applications.approve',
        '.financing.store', '.financing.apply', '.financing.self-finance.store',
        '.products.store', '.products.update',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user
            && (int) ($user->role ?? 0) === (int) USER_ROLE_OWNER
            && ! $request->isMethodSafe() // GET/HEAD/OPTIONS always allowed
            && $this->isGatedAction($request->route()?->getName())
            && $this->infraReadonly((int) $user->id)
        ) {
            $message = __('Your infrastructure bill is overdue — settle it to add or bill again. Your data stays fully viewable.');

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 423); // 423 Locked
            }

            return redirect()->back()->with('error', $message);
        }

        return $next($request);
    }

    private function isGatedAction(?string $routeName): bool
    {
        return $routeName !== null && Str::contains($routeName, $this->gated);
    }

    private function infraReadonly(int $ownerUserId): bool
    {
        try {
            // Cadence-aware: monthly owners' infra rides with the plan (blocks only
            // once it lapses); yearly owners block on overdue. See OwnerBillingStandingService.
            return app(OwnerBillingStandingService::class)->isReadonly($ownerUserId);
        } catch (\Throwable $e) {
            return false; // never block a legitimate action on an evaluation error
        }
    }
}
