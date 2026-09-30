<?php

namespace Tests\Feature\Tenant;

use App\Models\UserTour;
use App\Services\OnboardingTourService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** One-time onboarding-tour completion: shown until completed, then never again, idempotently. */
class OnboardingTourTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.tour_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
        ]]);
        config(['database.default' => 'tour_sqlite']);
        DB::purge('tour_sqlite');

        Schema::create('user_tours', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('tour_key', 60);
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'tour_key']);
        });
    }

    public function test_tour_shows_until_completed_then_never_again(): void
    {
        $svc = app(OnboardingTourService::class);

        $this->assertFalse($svc->completed(5, 'tenant_intro'));

        $svc->markComplete(5, 'tenant_intro');
        $this->assertTrue($svc->completed(5, 'tenant_intro'));

        // Idempotent — completing again doesn't create a duplicate row.
        $svc->markComplete(5, 'tenant_intro');
        $this->assertSame(1, UserTour::where('user_id', 5)->where('tour_key', 'tenant_intro')->count());
    }

    public function test_completion_is_per_user_and_per_tour(): void
    {
        $svc = app(OnboardingTourService::class);
        $svc->markComplete(5, 'tenant_intro');

        $this->assertFalse($svc->completed(6, 'tenant_intro'));   // a different user
        $this->assertFalse($svc->completed(5, 'owner_intro'));    // a different tour
    }
}
