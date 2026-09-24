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

    public function test_free_owner_with_rent_gets_the_transaction_pull_with_their_1pct(): void
    {
        $out = $this->svc()->rules([
            'pricingModel' => 'free', 'unitCap' => 30, 'unitsUsed' => 10,
            'rentGmv' => 200000.0, 'hasModule' => true, 'hasProperty' => true,
        ]);

        $this->assertContains('upgrade_transaction', $this->keys($out));
        $upgrade = collect($out)->firstWhere('key', 'upgrade_transaction');
        $this->assertSame(2000, $upgrade->meta['monthly_1pct']); // 1% of 200,000
        $this->assertStringContainsString('KES 2,000', $upgrade->body);
    }

    public function test_paid_owner_gets_no_transaction_upgrade_nudge(): void
    {
        $txn  = $this->svc()->rules(['pricingModel' => 'transaction', 'unitCap' => 1000, 'unitsUsed' => 40, 'rentGmv' => 500000.0, 'hasModule' => true, 'hasProperty' => true]);
        $this->assertNotContains('upgrade_transaction', $this->keys($txn));
    }

    public function test_owner_without_infra_gets_financing_and_with_infra_does_not(): void
    {
        $without = $this->svc()->rules(['pricingModel' => 'free', 'unitCap' => 30, 'unitsUsed' => 5, 'rentGmv' => 0.0, 'hasModule' => false, 'hasProperty' => true]);
        $this->assertContains('financing', $this->keys($without));

        $with = $this->svc()->rules(['pricingModel' => 'free', 'unitCap' => 30, 'unitsUsed' => 5, 'rentGmv' => 0.0, 'hasModule' => true, 'hasProperty' => true]);
        $this->assertNotContains('financing', $this->keys($with));

        $noProperty = $this->svc()->rules(['pricingModel' => 'free', 'unitCap' => 30, 'unitsUsed' => 0, 'rentGmv' => 0.0, 'hasModule' => false, 'hasProperty' => false]);
        $this->assertNotContains('financing', $this->keys($noProperty));
    }

    public function test_free_owner_near_cap_gets_informational_nudge(): void
    {
        $near = $this->svc()->rules(['pricingModel' => 'free', 'unitCap' => 30, 'unitsUsed' => 27, 'rentGmv' => 0.0, 'hasModule' => true, 'hasProperty' => true]);
        $this->assertContains('approaching_cap', $this->keys($near));

        $far = $this->svc()->rules(['pricingModel' => 'free', 'unitCap' => 30, 'unitsUsed' => 10, 'rentGmv' => 0.0, 'hasModule' => true, 'hasProperty' => true]);
        $this->assertNotContains('approaching_cap', $this->keys($far));
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
