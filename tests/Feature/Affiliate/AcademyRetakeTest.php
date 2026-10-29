<?php

namespace Tests\Feature\Affiliate;

use App\Http\Controllers\Admin\AcademyAdminController;
use App\Jobs\Mail\SendAcademyRetakeResetMail;
use App\Models\AcademyModule;
use App\Models\Affiliate;
use App\Models\AffiliateAcademyProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

/**
 * Locks the academy retake behaviour the user is relying on (can't be hand-walked across stages):
 *   - the admin Performance "Needs Review" badge reflects the ACTUAL per-module block flag, NOT just
 *     "used 3 attempts" — an affiliate who took all 3 tries but PASSED a module (and isn't certified
 *     yet) must NOT show as blocked (no module to reset, no admin was alerted);
 *   - a genuinely-blocked affiliate (3rd failure flagged the module) DOES show "Needs Review";
 *   - resetting a module clears the block AND dispatches the "you can continue now" email, so a
 *     discouraged affiliate who walked away learns the gate reopened.
 * Runs on the isolated affiliate sqlite harness (no live MySQL).
 */
class AcademyRetakeTest extends AffiliateDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function ($t) {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('email')->nullable();
            $t->integer('role')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('academy_modules', function ($t) {
            $t->id();
            $t->string('title');
            $t->text('content')->nullable();
            $t->string('youtube_url')->nullable();
            $t->integer('module_order')->default(1);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('affiliate_academy_progress', function ($t) {
            $t->id();
            $t->unsignedBigInteger('affiliate_id');
            $t->unsignedBigInteger('module_id');
            $t->integer('attempts')->default(0);
            $t->float('score')->nullable();
            $t->boolean('needs_review')->default(false);
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
    }

    private function affiliate(int $userId): Affiliate
    {
        User::create(['id' => $userId, 'first_name' => "U{$userId}", 'last_name' => 'Test', 'email' => "u{$userId}@test.dev", 'role' => USER_ROLE_AFFILIATE]);
        return Affiliate::create(['user_id' => $userId, 'referral_code' => "R{$userId}", 'status' => 1]);
    }

    private function module(int $order): AcademyModule
    {
        return AcademyModule::create(['title' => "Module {$order}", 'module_order' => $order, 'is_active' => true]);
    }

    public function test_used_all_attempts_but_passed_the_module_is_not_flagged_needs_review(): void
    {
        $m1 = $this->module(1);
        $this->module(2); // a 2nd active module → one passed module leaves them NOT certified

        // The "Duncan" case: 3 attempts on module 1, but PASSED it (completed, flag false). Not certified.
        $aff = $this->affiliate(201);
        AffiliateAcademyProgress::create([
            'affiliate_id' => $aff->id, 'module_id' => $m1->id,
            'attempts' => 3, 'score' => 90, 'needs_review' => false, 'completed_at' => now(),
        ]);

        $data = (new AcademyAdminController())->affiliatesPerformance()->getData();
        $row = collect($data['affiliates']->items())->firstWhere('id', $aff->id);

        $this->assertFalse($row['needs_review'], 'An affiliate who used 3 attempts but PASSED must not show Needs Review.');
        $this->assertFalse($row['certified'], 'Still has an unfinished module → not certified.');
        $this->assertSame(0, $data['summary']['needs_review']);
    }

    public function test_genuinely_blocked_affiliate_shows_needs_review_with_a_reset_target(): void
    {
        $m1 = $this->module(1);
        $this->module(2);

        $aff = $this->affiliate(202);
        AffiliateAcademyProgress::create([
            'affiliate_id' => $aff->id, 'module_id' => $m1->id,
            'attempts' => 3, 'score' => 40, 'needs_review' => true, 'completed_at' => null,
        ]);

        $data = (new AcademyAdminController())->affiliatesPerformance()->getData();
        $row = collect($data['affiliates']->items())->firstWhere('id', $aff->id);

        $this->assertTrue($row['needs_review']);
        $this->assertSame($m1->id, $row['module_id'], 'The reset button must target the flagged module.');
        $this->assertSame(1, $data['summary']['needs_review']);
    }

    public function test_reset_clears_the_block_and_dispatches_the_continue_email(): void
    {
        Queue::fake();

        $m1 = $this->module(1);
        $aff = $this->affiliate(203);
        AffiliateAcademyProgress::create([
            'affiliate_id' => $aff->id, 'module_id' => $m1->id,
            'attempts' => 3, 'score' => 40, 'needs_review' => true, 'completed_at' => null,
        ]);

        (new AcademyAdminController())->resetAffiliateModule($aff->id, $m1->id);

        $progress = AffiliateAcademyProgress::where('affiliate_id', $aff->id)->where('module_id', $m1->id)->first();
        $this->assertSame(0, (int) $progress->attempts);
        $this->assertFalse((bool) $progress->needs_review);
        $this->assertNull($progress->completed_at);
        $this->assertNull($progress->score);

        Queue::assertPushed(SendAcademyRetakeResetMail::class, function ($job) use ($aff) {
            return $job->affiliateId === $aff->id;
        });
    }
}
