<?php

namespace App\Services\OwnerAdvisor;

use App\Models\OwnerSuggestionDismissal;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Schema;

/**
 * The Owner Upgrade Advisor — honest, personalised in-app nudges that pull an owner toward the
 * domain that is genuinely best for them (see the "Rails, Not Seats" pricing note). Suggestions
 * are computed LIVE from the owner's current state (plan, units, rent, infra), so they are always
 * accurate and never go stale; the ONLY persisted state is a per-owner dismissal/snooze.
 *
 * A DELIBERATELY SEPARATE rail from the affiliate lead-suggestion engine — no shared tables,
 * models or services — so the two can never entangle or regress each other.
 */
class OwnerAdvisorService
{
    /** Default snooze when an owner dismisses a nudge (days). */
    private const SNOOZE_DAYS = 30;

    /** All currently-visible suggestions for an owner, highest priority first. */
    public function suggestionsFor(int $userId): array
    {
        $candidates = $this->rules($this->state($userId));

        // Drop anything the owner has dismissed and not yet un-snoozed.
        $hidden  = $this->activeDismissalKeys($userId);
        $visible = array_filter($candidates, fn (OwnerSuggestion $s) => ! in_array($s->key, $hidden, true));

        usort($visible, fn (OwnerSuggestion $a, OwnerSuggestion $b) => $b->weight() <=> $a->weight());

        return array_values($visible);
    }

    /**
     * Run the rules against a supplied state → the candidate suggestions (pure; no DB). Public so the
     * rule logic is directly testable with crafted states, independent of the plan-join in state().
     *
     * @return array<int,OwnerSuggestion>
     */
    public function rules(array $state): array
    {
        // Order matters for ties (equal priority): VALUE-EXPOSURE first — the philosophy is to show
        // owners the value already in the platform (SMS, marketplace, agreements, screening), which
        // earns the upgrade; the Transaction/financing push comes after. As an owner adopts a feature
        // its nudge stops firing and the next surfaces, so the advisor walks them through the value.
        return array_values(array_filter([
            $this->ruleAddProducts($state),
            $this->ruleTrySms($state),
            $this->ruleTryAgreements($state),
            $this->ruleTryScreening($state),
            $this->ruleUpgradeTransaction($state),
            $this->ruleFinancing($state),
            $this->ruleApproachingCap($state),
        ]));
    }

    /** The top N suggestions (for a compact dashboard surface). */
    public function topFor(int $userId, int $limit = 1): array
    {
        return array_slice($this->suggestionsFor($userId), 0, max(1, $limit));
    }

    /** Dismiss/snooze one suggestion for an owner (cross-device, unlike the old localStorage). */
    public function dismiss(int $userId, string $key, ?int $days = self::SNOOZE_DAYS): void
    {
        try {
            if (! Schema::hasTable('owner_suggestion_dismissals')) {
                return;
            }
            OwnerSuggestionDismissal::updateOrCreate(
                ['owner_user_id' => $userId, 'suggestion_key' => $key],
                ['snoozed_until' => $days ? now()->addDays($days) : null]
            );
        } catch (\Throwable $e) {
            // a dismiss never breaks the page
        }
    }

    /** Suggestion keys currently hidden for this owner (indefinite, or snooze not yet elapsed). */
    public function activeDismissalKeys(int $userId): array
    {
        try {
            if (! Schema::hasTable('owner_suggestion_dismissals')) {
                return [];
            }

            return OwnerSuggestionDismissal::where('owner_user_id', $userId)->get()
                ->filter(fn (OwnerSuggestionDismissal $d) => $d->isActive())
                ->pluck('suggestion_key')->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * The owner's current, advisor-relevant state — computed once per request. Fully guarded so a
     * missing table/column (bare install) degrades to "no suggestions", never a broken dashboard.
     *
     * @return array{pricingModel:string,unitCap:int,unitsUsed:int,rentGmv:float,hasModule:bool,hasProperty:bool}
     */
    private function state(int $userId): array
    {
        $default = [
            'pricingModel' => 'free', 'unitCap' => 30, 'unitsUsed' => 0, 'rentGmv' => 0.0,
            'hasModule' => false, 'hasProperty' => false, 'hasTenants' => false,
            'hasProducts' => true, 'hasAgreements' => true, 'hasScreened' => true, 'usedSms' => true,
        ];

        try {
            $plan = app(SubscriptionService::class)->getCurrentPlan($userId);

            $unitsUsed = PropertyUnit::whereHas('property', fn ($q) => $q->where('owner_user_id', $userId))->count();
            $rentGmv   = (float) Tenant::where('owner_user_id', $userId)
                ->where('status', TENANT_STATUS_ACTIVE)->sum('general_rent');
            $hasTenants = Tenant::where('owner_user_id', $userId)->where('status', TENANT_STATUS_ACTIVE)->exists();

            // Scope modules via the owner's properties (property_modules has no owner_user_id and
            // its `owner_id` semantics are ambiguous — property.owner_user_id is unambiguous).
            $propertyIds = Property::where('owner_user_id', $userId)->pluck('id');
            $hasProperty = $propertyIds->isNotEmpty();

            $hasModule = false;
            if ($hasProperty && Schema::hasTable('property_modules')) {
                $hasModule = \App\Centresidence\Models\PropertyModule::whereIn('property_id', $propertyIds)
                    ->where('status', \App\Centresidence\Models\PropertyModule::STATUS_ACTIVE)->exists();
            }

            // "Has the owner tried feature X?" — drives the value-exposure nudges. Default true (no
            // nudge) so a missing table never nags. exists() calls are cheap (indexed owner scope).
            $has = fn (string $table, callable $q) => Schema::hasTable($table) && $q();
            $hasProducts   = $has('products', fn () => \App\Models\Product::where('owner_user_id', $userId)->exists());
            $hasAgreements = $has('agreements', fn () => \App\Models\Agreement::where('owner_user_id', $userId)->exists());
            $hasScreened   = $has('tenant_screening_lookups', fn () => \Illuminate\Support\Facades\DB::table('tenant_screening_lookups')->where('owner_user_id', $userId)->exists());
            $usedSms       = $has('owner_credit_transactions', fn () => \Illuminate\Support\Facades\DB::table('owner_credit_transactions')->where('owner_user_id', $userId)->where('bucket', 'sms')->exists());

            return [
                'pricingModel' => $plan->pricing_model ?? 'free',
                'unitCap'      => (int) ($plan->max_unit ?? 30),
                'unitsUsed'    => (int) $unitsUsed,
                'rentGmv'      => $rentGmv,
                'hasModule'    => $hasModule,
                'hasProperty'  => $hasProperty,
                'hasTenants'   => (bool) $hasTenants,
                'hasProducts'  => (bool) $hasProducts,
                'hasAgreements'=> (bool) $hasAgreements,
                'hasScreened'  => (bool) $hasScreened,
                'usedSms'      => (bool) $usedSms,
            ];
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /** Resolve a route by name, degrading to home if it isn't registered (never throws). */
    private function url(string $name): string
    {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name) : url('/');
    }

    // ── Value-exposure rules (free owners) — surface the value already in the platform ──────────
    // Each fires only when the feature is UNUSED and the owner has the context to benefit; as soon
    // as they try it the nudge stops and the next value nudge surfaces.

    /** Free owner with a property but no shop → open a marketplace (an income stream). */
    private function ruleAddProducts(array $s): ?OwnerSuggestion
    {
        if ($s['pricingModel'] !== 'free' || ! $s['hasProperty'] || $s['hasProducts']) {
            return null;
        }

        return new OwnerSuggestion(
            key: 'try_marketplace', category: 'value', priority: 'medium',
            title: __('Earn more: open a shop for your tenants'),
            body: __('List products your tenants need — water, gas, essentials — and sell them right inside the app. An extra income stream, no extra platform.'),
            ctaLabel: __('Set up My Shop'),
            ctaUrl: $this->url('owner.products.index'),
            meta: []
        );
    }

    /** Free owner with tenants who hasn't used SMS → reach tenants instantly. */
    private function ruleTrySms(array $s): ?OwnerSuggestion
    {
        if ($s['pricingModel'] !== 'free' || ! $s['hasTenants'] || $s['usedSms']) {
            return null;
        }

        return new OwnerSuggestion(
            key: 'try_sms', category: 'value', priority: 'medium',
            title: __('Reach your tenants instantly by SMS'),
            body: __('Send rent reminders, receipts and notices straight to their phones — the surest way to get paid on time. Top up SMS credits to start.'),
            ctaLabel: __('Explore SMS'),
            ctaUrl: $this->url('owner.sms.credits.index'),
            meta: []
        );
    }

    /** Free owner with tenants but no agreements → send a lease to e-sign. */
    private function ruleTryAgreements(array $s): ?OwnerSuggestion
    {
        if ($s['pricingModel'] !== 'free' || ! $s['hasTenants'] || $s['hasAgreements']) {
            return null;
        }

        return new OwnerSuggestion(
            key: 'try_agreements', category: 'value', priority: 'medium',
            title: __('Sign leases in-app — no printing'),
            body: __('Send a tenancy agreement and have your tenant sign it on their phone, with a certified copy on file. Faster, safer, paperless.'),
            ctaLabel: __('Send an agreement'),
            ctaUrl: $this->url('owner.agreement.index'),
            meta: []
        );
    }

    /** Free owner with tenants who hasn't screened → check a tenant's payment record. */
    private function ruleTryScreening(array $s): ?OwnerSuggestion
    {
        if ($s['pricingModel'] !== 'free' || ! $s['hasTenants'] || $s['hasScreened']) {
            return null;
        }

        return new OwnerSuggestion(
            key: 'try_screening', category: 'value', priority: 'medium',
            title: __('Know a tenant before you sign'),
            body: __('Screening shows a prospective tenant\'s objective rental payment record — on-time history, arrears and more — so you rent with confidence.'),
            ctaLabel: __('Try screening'),
            ctaUrl: $this->url('owner.screening.index'),
            meta: []
        );
    }

    // ── Upgrade / growth rules ──────────────────────────────────────────────────

    /** Free owner with real rent → the Transaction pull (their 1% cost, plus what it unlocks). */
    private function ruleUpgradeTransaction(array $s): ?OwnerSuggestion
    {
        if ($s['pricingModel'] !== 'free' || $s['rentGmv'] <= 0) {
            return null;
        }

        $monthly = (int) round($s['rentGmv'] * 0.01);

        return new OwnerSuggestion(
            key: 'upgrade_transaction',
            category: 'upgrade',
            priority: 'medium',
            title: __('Get more from Centresidence as you grow'),
            body: __('Collecting rent through Transaction costs about :amt/mo for you — and unlocks financing (repaid from rent), lower marketplace fees and an in-app wallet.', ['amt' => 'KES ' . number_format($monthly)]),
            ctaLabel: __('See Transaction'),
            ctaUrl: $this->url('owner.subscription.index'),
            meta: ['rent_gmv' => $s['rentGmv'], 'monthly_1pct' => $monthly]
        );
    }

    /** No smart infrastructure yet → finance meters/locks (the Finance-OS magnet, repaid from rent). */
    private function ruleFinancing(array $s): ?OwnerSuggestion
    {
        if ($s['hasModule'] || ! $s['hasProperty']) {
            return null;
        }

        return new OwnerSuggestion(
            key: 'financing',
            category: 'financing',
            priority: 'medium',
            title: __('Boost your property cashflow with smart modules'),
            body: __('Add water/gas meters, smart locks and more — finance them through a partner (repaid from rent) or self-finance and own them outright.'),
            ctaLabel: __('Explore modules'),
            ctaUrl: $this->url('owner.financing.index'),
            meta: []
        );
    }

    /** Free owner nearing the 30-unit ceiling → informational, never punitive. */
    private function ruleApproachingCap(array $s): ?OwnerSuggestion
    {
        if ($s['pricingModel'] !== 'free' || $s['unitsUsed'] < 25) {
            return null;
        }

        return new OwnerSuggestion(
            key: 'approaching_cap',
            category: 'cap',
            priority: 'medium',
            title: __('You are at :used of :cap free units', ['used' => $s['unitsUsed'], 'cap' => $s['unitCap']]),
            body: __('As your portfolio grows, a paid plan lowers your fees and unlocks financing. See what each plan adds — no rush.'),
            ctaLabel: __('Compare plans'),
            ctaUrl: $this->url('owner.subscription.index'),
            meta: ['units_used' => $s['unitsUsed'], 'unit_cap' => $s['unitCap']]
        );
    }
}
