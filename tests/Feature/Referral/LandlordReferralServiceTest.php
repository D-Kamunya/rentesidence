<?php

namespace Tests\Feature\Referral;

use App\Models\LandlordReferral;
use App\Models\Lead;
use App\Models\User;
use App\Services\LandlordReferralService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Behaviour tests for the invite-a-landlord funnel engine (Slice 1: the foundation).
 *
 * Runs against an isolated in-memory sqlite connection carrying only the tables the flow
 * touches — never the real database. Locks the money-critical rules: a referral is rewarded
 * at most once, only a referral tied to a real owner confirms, the holding period gates
 * payability, clawback reverses an unpaid reward, and graduation counts confirmed referrals.
 */
class LandlordReferralServiceTest extends TestCase
{
    private LandlordReferralService $svc;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.ref_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        config(['database.default' => 'ref_sqlite']);
        DB::purge('ref_sqlite');

        // Deterministic funnel config for the assertions below.
        config([
            'referrals.enabled'                        => true,
            'referrals.cash_enabled'                   => true,
            'referrals.cash_amount'                    => 200,
            'referrals.currency'                       => 'KES',
            'referrals.hold_days'                      => 30,
            'referrals.graduation_threshold'           => 3,
            'referrals.anti_abuse.max_invites_per_day' => 3,
            'referrals.anti_abuse.manual_review_after' => 10,
        ]);

        Schema::create('users', function ($t) {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('contact_number')->nullable();
            $t->string('email')->nullable();
            $t->unsignedTinyInteger('role')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('leads', function ($t) {
            $t->id();
            $t->unsignedBigInteger('owner_id')->nullable();
            $t->string('status')->nullable();
            $t->timestamp('ownership_expires_at')->nullable(); // set by Lead::creating hook
            $t->timestamps();
        });

        Schema::create('landlord_referral_codes', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('code', 32);
            $t->timestamps();
            $t->unique('user_id');
            $t->unique('code');
        });

        Schema::create('landlord_referrals', function ($t) {
            $t->id();
            $t->unsignedBigInteger('referrer_user_id');
            $t->string('code', 32);
            $t->string('invitee_name')->nullable();
            $t->string('invitee_phone')->nullable();
            $t->string('invitee_email')->nullable();
            $t->string('invitee_company')->nullable();
            $t->string('status', 20)->default('pending');
            $t->unsignedBigInteger('lead_id')->nullable();
            $t->unsignedBigInteger('owner_id')->nullable();
            $t->string('reward_type', 12)->nullable();
            $t->decimal('reward_amount', 12, 2)->default(0);
            $t->string('currency', 8)->nullable();
            $t->string('trigger_reason', 30)->nullable();
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamp('held_until')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamp('clawed_back_at')->nullable();
            $t->boolean('needs_review')->default(false);
            $t->json('meta')->nullable();
            $t->timestamps();
        });

        $this->svc = new LandlordReferralService();
    }

    private function tenant(array $attrs = []): User
    {
        return User::create(array_merge([
            'first_name' => 'Tess', 'last_name' => 'Tenant',
            'contact_number' => '254700111222', 'email' => 'tess@example.com', 'role' => USER_ROLE_TENANT,
        ], $attrs));
    }

    public function test_code_is_stable_per_tenant(): void
    {
        $tenant = $this->tenant();

        $a = $this->svc->codeForTenant($tenant->id);
        $b = $this->svc->codeForTenant($tenant->id);

        $this->assertSame($a, $b, 'A tenant keeps one stable invite code.');
        $this->assertSame($tenant->id, $this->svc->resolveReferrer($a)->id);
    }

    public function test_start_invite_records_pending_and_respects_daily_cap(): void
    {
        $tenant = $this->tenant();

        for ($i = 0; $i < 3; $i++) {
            $this->assertNotNull($this->svc->startInvite($tenant, ['name' => "L$i", 'phone' => "25470000000$i"]));
        }

        // Cap is 3/day — the 4th is refused (nothing written).
        $this->assertNull($this->svc->startInvite($tenant, ['name' => 'L4', 'phone' => '254700000004']));
        $this->assertSame(3, LandlordReferral::where('referrer_user_id', $tenant->id)->count());
        $this->assertSame(3, LandlordReferral::where('status', LandlordReferral::STATUS_PENDING)->count());
    }

    public function test_attach_lead_links_pending_invite_and_is_idempotent_per_lead(): void
    {
        $tenant = $this->tenant();
        $code = $this->svc->codeForTenant($tenant->id);
        $this->svc->startInvite($tenant, ['name' => 'Larry', 'phone' => '254733444555']);

        $lead = Lead::create(['status' => 'active']);
        $ref = $this->svc->attachLead($code, $lead, ['phone' => '254733444555']);

        $this->assertSame(LandlordReferral::STATUS_LEAD_CREATED, $ref->status);
        $this->assertSame($lead->id, $ref->lead_id);
        // Reused the pending invite rather than opening a second row.
        $this->assertSame(1, LandlordReferral::where('referrer_user_id', $tenant->id)->count());

        // Attaching the same lead again returns the same row, no duplicate.
        $again = $this->svc->attachLead($code, $lead, ['phone' => '254733444555']);
        $this->assertSame($ref->id, $again->id);
        $this->assertSame(1, LandlordReferral::where('referrer_user_id', $tenant->id)->count());
    }

    public function test_confirm_is_once_only_and_pays_cash_with_holding_period(): void
    {
        $tenant = $this->tenant();
        $code = $this->svc->codeForTenant($tenant->id);
        $lead = Lead::create(['status' => 'converted', 'owner_id' => 42]);
        $this->svc->attachLead($code, $lead, ['name' => 'Odele Owner']);

        $ref = $this->svc->confirmForOwner(42, 'first_subscription');

        $this->assertSame(LandlordReferral::STATUS_CONFIRMED, $ref->status);
        $this->assertSame(LandlordReferral::REWARD_CASH, $ref->reward_type);
        $this->assertEquals(200.0, (float) $ref->reward_amount);
        $this->assertSame('KES', $ref->currency);
        $this->assertSame(42, $ref->owner_id);
        $this->assertNotNull($ref->held_until);
        $this->assertTrue($ref->held_until->isFuture(), 'Reward is held before it is payable.');

        // Idempotent: a second confirm does nothing.
        $this->assertNull($this->svc->confirmForOwner(42, 'revenue_threshold'));
        $this->assertSame(1, LandlordReferral::where('status', LandlordReferral::STATUS_CONFIRMED)->count());
    }

    public function test_phantom_owner_with_no_referral_confirms_nothing(): void
    {
        $this->assertNull($this->svc->confirmForOwner(999, 'first_subscription'));
    }

    public function test_payable_balance_respects_holding_period_then_clawback_reverses(): void
    {
        $tenant = $this->tenant();
        $code = $this->svc->codeForTenant($tenant->id);
        $lead = Lead::create(['status' => 'converted', 'owner_id' => 7]);
        $this->svc->attachLead($code, $lead);
        $this->svc->confirmForOwner(7, 'first_subscription');

        // Still inside the hold window → not payable yet.
        $this->assertEquals(0.0, $this->svc->payableBalance($tenant->id));

        // Fast-forward the hold to the past → now payable.
        LandlordReferral::where('owner_id', 7)->update(['held_until' => now()->subDay()]);
        $this->assertEquals(200.0, $this->svc->payableBalance($tenant->id));

        // Clawback (owner churned) reverses it and it leaves the payable balance.
        $clawed = $this->svc->clawback(7, 'refund');
        $this->assertSame(LandlordReferral::STATUS_CLAWED_BACK, $clawed->status);
        $this->assertEquals(0.0, $this->svc->payableBalance($tenant->id));
    }

    public function test_graduation_counts_confirmed_referrals(): void
    {
        $tenant = $this->tenant();
        $code = $this->svc->codeForTenant($tenant->id);

        foreach ([101, 102, 103] as $i => $ownerId) {
            $lead = Lead::create(['status' => 'converted', 'owner_id' => $ownerId]);
            $this->svc->attachLead($code, $lead, ['phone' => "2547010101$i"]);
            $this->assertFalse($this->svc->graduationEligible($tenant->id), "not yet eligible at $i");
            $this->svc->confirmForOwner($ownerId, 'first_subscription');
        }

        $this->assertSame(3, $this->svc->confirmedCount($tenant->id));
        $this->assertTrue($this->svc->graduationEligible($tenant->id));
    }

    public function test_self_referral_is_flagged_for_review(): void
    {
        $tenant = $this->tenant(['contact_number' => '254799888777']);
        $code = $this->svc->codeForTenant($tenant->id);
        // Invited "landlord" shares the tenant's own phone → shared-instrument smell.
        $this->svc->startInvite($tenant, ['name' => 'Me Myself', 'phone' => '254799888777']);
        $lead = Lead::create(['status' => 'converted', 'owner_id' => 55]);
        $this->svc->attachLead($code, $lead, ['phone' => '254799888777']);

        $ref = $this->svc->confirmForOwner(55, 'first_subscription');

        $this->assertTrue($ref->needs_review, 'A self-referral is held for manual review.');
        $this->assertEquals(0.0, $this->svc->payableBalance($tenant->id), 'Flagged rewards are not auto-payable.');
    }
}
