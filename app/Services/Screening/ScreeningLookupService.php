<?php

namespace App\Services\Screening;

use App\Models\TenantCreditProfile;
use App\Models\TenantScreeningLookup;
use App\Services\Credit\CreditService;
use Illuminate\Support\Facades\DB;

/**
 * Owner-facing tenant screening (Step 4 of the Global Tenant ID). Runs a paid/metered lookup
 * that returns the OBJECTIVE aggregated score + summary for the person behind a phone number,
 * and logs the access (bureau + transparency posture: the tenant can see who screened them
 * and dispute, but the objective record is not gated behind their consent).
 *
 * Coverage mirrors the agreement bucket ([[credit-rail-unified]]):
 *   • standalone install (no SaaS)      → unlimited
 *   • subscription / transaction plan   → unlimited (included)
 *   • free plan                         → a small monthly free allowance, then purchased
 *                                         screening credits (one per lookup)
 *
 * FAIRNESS RULE: a MISS (no record anywhere in the system) is never charged — you only spend
 * a credit when there's an actual record to see. Eligibility is still required to run any
 * lookup, which stops free phone-number enumeration.
 */
class ScreeningLookupService
{
    /**
     * Resolve how a lookup would be covered for this owner, and whether they may run one.
     *
     * @return array{allowed:bool, requiresPayment:bool, plan:string, cover:?string,
     *               quota?:int, freeUsed?:int, remaining?:int, credits?:int, price?:float}
     */
    public function eligibility(int $ownerUserId): array
    {
        // No SaaS layer → single-operator install, no metering.
        if (isAddonInstalled('PROTYSAAS') < 1) {
            return ['allowed' => true, 'requiresPayment' => false, 'plan' => 'standalone', 'cover' => 'plan'];
        }

        $plan = optional(ownerCurrentPackage($ownerUserId))->pricing_model ?: 'free';

        if (in_array($plan, ['subscription', 'transaction'], true)) {
            return ['allowed' => true, 'requiresPayment' => false, 'plan' => $plan, 'cover' => 'plan'];
        }

        // Free plan: a MONTHLY free allowance (use-it-or-lose-it, counted from this month's
        // free-billed lookups — no cron), then PURCHASED credits (roll over).
        $quota    = max(0, (int) getOption('screening_free_quota', 3));
        $freeUsed = TenantScreeningLookup::where('owner_user_id', $ownerUserId)
            ->where('billed_as', 'free')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $freeRemaining = max(0, $quota - $freeUsed);
        $credits = CreditService::balance('screening', $ownerUserId);

        $base = [
            'plan' => 'free', 'quota' => $quota, 'freeUsed' => $freeUsed,
            'remaining' => $freeRemaining, 'credits' => $credits,
            'price' => CreditService::pricePerUnit('screening'),
        ];

        if ($freeRemaining > 0) {
            return array_merge($base, ['allowed' => true, 'requiresPayment' => false, 'cover' => 'free']);
        }
        if ($credits > 0) {
            return array_merge($base, ['allowed' => true, 'requiresPayment' => false, 'cover' => 'credit']);
        }

        return array_merge($base, ['allowed' => false, 'requiresPayment' => true, 'cover' => null]);
    }

    /**
     * Run a screening lookup for $phone on behalf of $ownerUserId. Enforces the gate, computes
     * the objective profile, charges per coverage ONLY on a hit, and logs the access.
     *
     * @return array{status:string, profile:?TenantCreditProfile, lookup:TenantScreeningLookup, phone:string}
     *         status: 'ok' (record shown, possibly charged) | 'no_record' (miss, not charged)
     */
    public function screen(int $ownerUserId, string $phone): array
    {
        $elig = $this->eligibility($ownerUserId);
        if (! $elig['allowed']) {
            throw new \RuntimeException(__("You've used your free screenings for this month. Top up screening credits to run more, or upgrade your plan."));
        }

        $profiles   = new TenantCreditProfileService();
        $normalized = $profiles->normalizePhone($phone);
        if ($normalized === '') {
            throw new \RuntimeException(__('Enter a valid phone number to screen.'));
        }

        $profile = $profiles->computeForIdentity($normalized);

        // MISS — nobody with this phone has any tenancy on record. Never charge; log for audit.
        if (! $profile) {
            $lookup = TenantScreeningLookup::create([
                'owner_user_id' => $ownerUserId,
                'identity_key'  => $normalized,
                'phone'         => $normalized,
                'billed_as'     => 'none',
            ]);
            return ['status' => 'no_record', 'profile' => null, 'lookup' => $lookup, 'phone' => $normalized];
        }

        // HIT — consume per coverage, atomically with the access-log row so a failed deduct
        // rolls back the log too (deductOne nests as a savepoint).
        $cover = $elig['cover'] ?? 'plan';

        $lookup = DB::transaction(function () use ($ownerUserId, $normalized, $profile, $cover) {
            if ($cover === 'credit'
                && ! CreditService::deductOne('screening', $ownerUserId, 'Tenant screening lookup')) {
                // Balance emptied since the eligibility check (race) — refuse rather than
                // hand over a report unpaid.
                throw new \RuntimeException(__('Your screening credits ran out. Please top up and try again.'));
            }

            return TenantScreeningLookup::create([
                'owner_user_id'            => $ownerUserId,
                'identity_key'             => $profile->identity_key,
                'phone'                    => $normalized,
                'tenant_credit_profile_id' => $profile->id,
                'score'                    => $profile->score,
                'score_band'               => $profile->score_band,
                'score_grade'              => $profile->score_grade,
                'was_thin_file'            => (bool) $profile->is_thin_file,
                'was_activated'            => ! empty($profile->activated_at),
                'billed_as'                => $cover,
            ]);
        });

        return ['status' => 'ok', 'profile' => $profile, 'lookup' => $lookup, 'phone' => $normalized];
    }

    /**
     * Whether this owner's screening is UNLIMITED (plan-covered) rather than metered — a
     * standalone install, or a subscription/transaction plan. Static so config/credits.php can
     * reference it as a callable (drives the "Unlimited (included in your plan)" display).
     */
    public static function ownerHasUnlimited(int $ownerUserId): bool
    {
        if (isAddonInstalled('PROTYSAAS') < 1) {
            return true; // single-operator install — no metering
        }
        $plan = optional(ownerCurrentPackage($ownerUserId))->pricing_model ?: 'free';
        return in_array($plan, ['subscription', 'transaction'], true);
    }

    /**
     * The free-plan owner's MONTHLY free screening allowance for display. Returns null when it
     * doesn't apply (unlimited plan). Static so config/credits.php can reference it as a
     * callable.
     *
     * @return array{quota:int, used:int, remaining:int}|null
     */
    public static function freeMonthlyAllowance(int $ownerUserId): ?array
    {
        if (self::ownerHasUnlimited($ownerUserId)) {
            return null;
        }
        $elig = (new self())->eligibility($ownerUserId);
        if (($elig['plan'] ?? null) !== 'free') {
            return null;
        }
        return [
            'quota'     => (int) ($elig['quota'] ?? 0),
            'used'      => (int) ($elig['freeUsed'] ?? 0),
            'remaining' => (int) ($elig['remaining'] ?? 0),
        ];
    }
}
