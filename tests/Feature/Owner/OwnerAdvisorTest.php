<?php

namespace Tests\Feature\Owner;

use App\Models\OwnerSuggestionDismissal;
use App\Services\OwnerAdvisor\OwnerAdvisorService;
use App\Services\OwnerAdvisor\OwnerSuggestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The Owner Upgrade Advisor — the rule logic (pure, from a crafted state) and the dismissal/snooze
 * lifecycle. Isolated in-memory sqlite for the one persisted table; the rules touch no DB.
 * A distinct rail from the affiliate lead-suggestion engine (which these tests never touch).
 */
class OwnerAdvisorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.adv_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
        ]]);
        config(['database.default' => 'adv_sqlite']);
        DB::purge('adv_sqlite');
        \Illuminate\Database\Eloquent\Model::unguard();

        Schema::create('owner_suggestion_dismissals', function ($t) {
            $t->id();
            $t->unsignedBigInteger('owner_user_id');
            $t->string('suggestion_key', 60);
            $t->timestamp('snoozed_until')->nullable();
            $t->timestamps();
            $t->unique(['owner_user_id', 'suggestion_key']);
        });
    }

    private function svc(): OwnerAdvisorService
    {
        return app(OwnerAdvisorService::class);
    }

    /** @param array<int,OwnerSuggestion> $suggestions */
    private function keys(array $suggestions): array
    {
        return array_map(fn (OwnerSuggestion $s) => $s->key, $suggestions);
    }

    /** Full state with all features "already used" (so value rules stay quiet unless a test opts in). */
    private function st(array $o = []): array
    {
        return array_merge([
            'pricingModel' => 'free', 'unitCap' => 30, 'unitsUsed' => 10, 'rentGmv' => 0.0,
            'hasModule' => true, 'hasProperty' => true, 'hasTenants' => true,
            'hasProducts' => true, 'hasAgreements' => true, 'hasScreened' => true, 'usedSms' => true,
        ], $o);
    }

    public function test_free_owner_with_rent_gets_the_transaction_pull_with_their_1pct(): void
    {
        $out = $this->svc()->rules($this->st(['rentGmv' => 200000.0]));

        $this->assertContains('upgrade_transaction', $this->keys($out));
        $upgrade = collect($out)->firstWhere('key', 'upgrade_transaction');
        $this->assertSame(2000, $upgrade->meta['monthly_1pct']); // 1% of 200,000
        $this->assertStringContainsString('KES 2,000', $upgrade->body);
    }

    public function test_paid_owner_gets_no_transaction_upgrade_nudge(): void
    {
        $txn  = $this->svc()->rules($this->st(['pricingModel' => 'transaction', 'rentGmv' => 500000.0]));
        $this->assertNotContains('upgrade_transaction', $this->keys($txn));
    }

    public function test_owner_without_infra_gets_financing_and_with_infra_does_not(): void
    {
        $without = $this->svc()->rules($this->st(['hasModule' => false, 'hasProperty' => true]));
        $this->assertContains('financing', $this->keys($without));

        $with = $this->svc()->rules($this->st(['hasModule' => true, 'hasProperty' => true]));
        $this->assertNotContains('financing', $this->keys($with));

        $noProperty = $this->svc()->rules($this->st(['hasModule' => false, 'hasProperty' => false]));
        $this->assertNotContains('financing', $this->keys($noProperty));
    }

    public function test_free_owner_near_cap_gets_informational_nudge(): void
    {
        $near = $this->svc()->rules($this->st(['unitsUsed' => 27]));
        $this->assertContains('approaching_cap', $this->keys($near));

        $far = $this->svc()->rules($this->st(['unitsUsed' => 10]));
        $this->assertNotContains('approaching_cap', $this->keys($far));
    }

    public function test_value_rules_fire_only_when_the_feature_is_unused_and_context_exists(): void
    {
        // Free owner, tenants present, nothing tried yet → all four value nudges fire, value-first.
        $all = $this->svc()->rules($this->st([
            'rentGmv' => 100000.0, // so the transaction push also fires, to test ordering
            'hasProducts' => false, 'usedSms' => false, 'hasAgreements' => false, 'hasScreened' => false,
        ]));
        $keys = $this->keys($all);
        foreach (['try_marketplace', 'try_sms', 'try_agreements', 'try_screening'] as $k) {
            $this->assertContains($k, $keys);
        }
        // Value exposure ranks ABOVE the transaction push in the ordering.
        $this->assertLessThan(array_search('upgrade_transaction', $keys), array_search('try_marketplace', $keys));

        // Using a feature silences its nudge; SMS needs tenants; a paid owner sees no value nudges.
        $this->assertNotContains('try_sms', $this->keys($this->svc()->rules($this->st(['usedSms' => true, 'hasProducts' => false]))));
        $this->assertNotContains('try_sms', $this->keys($this->svc()->rules($this->st(['hasTenants' => false, 'usedSms' => false]))));
        $this->assertNotContains('try_marketplace', $this->keys($this->svc()->rules($this->st(['pricingModel' => 'transaction', 'hasProducts' => false]))));
    }

    public function test_dismiss_hides_a_suggestion_and_snooze_expiry_reveals_it(): void
    {
        $svc = $this->svc();

        $svc->dismiss(7, 'financing', 30);
        $this->assertContains('financing', $svc->activeDismissalKeys(7));

        // A snooze in the past no longer hides it (it re-appears).
        OwnerSuggestionDismissal::where('owner_user_id', 7)->where('suggestion_key', 'financing')
            ->update(['snoozed_until' => now()->subDay()]);
        $this->assertNotContains('financing', $svc->activeDismissalKeys(7));

        // An indefinite dismissal (null snooze) keeps hiding it.
        $svc->dismiss(7, 'approaching_cap', null);
        $this->assertContains('approaching_cap', $svc->activeDismissalKeys(7));
    }
}
