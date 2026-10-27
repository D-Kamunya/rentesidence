<?php

namespace Tests\Feature\Affiliate;

use App\Jobs\Mail\SendFreeInviteMail;
use App\Models\Company;
use App\Models\Lead;
use App\Models\User;
use App\Services\FreeOnboardingService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * FreeOnboardingService — the consent-gated "Set up on Free" invite lifecycle. Runs on an
 * independent in-memory SQLite connection (no local MySQL needed). Covers invite-state derivation,
 * the can-invite gating, invite creation + the anti-phantom pending cap, and the accept-time
 * guards. The full accept→account-creation path uses the heavy owner-onboarding stack and is left
 * to higher-level verification.
 */
class FreeOnboardingServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'free_sqlite',
            'database.connections.free_sqlite' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('free_sqlite');
        DB::setDefaultConnection('free_sqlite');
        $this->createTables();
        Bus::fake();
    }

    private function createTables(): void
    {
        Schema::create('companies', function ($t) {
            $t->id();
            $t->string('company_name')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('sales_status')->nullable();
            $t->timestamps();
        });
        Schema::create('leads', function ($t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->nullable();
            $t->unsignedBigInteger('affiliate_id')->nullable();
            $t->unsignedBigInteger('owner_id')->nullable();
            $t->string('contact_person_name')->nullable();
            $t->string('status')->nullable();
            $t->timestamp('last_activity_at')->nullable();
            $t->timestamp('converted_at')->nullable();
            $t->string('free_invite_token', 64)->nullable();
            $t->string('free_invite_email')->nullable();
            $t->timestamp('free_invite_sent_at')->nullable();
            $t->timestamp('free_invite_expires_at')->nullable();
            $t->timestamp('free_invite_accepted_at')->nullable();
            $t->unsignedTinyInteger('free_invite_count')->default(0);
            $t->timestamp('ownership_expires_at')->nullable();
            $t->timestamps();
        });
        Schema::create('lead_activities', function ($t) {
            $t->id();
            $t->unsignedBigInteger('lead_id');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('type');
            $t->text('description')->nullable();
            $t->timestamps();
        });
        Schema::create('users', function ($t) {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('email')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function ($t) {
                $t->id();
                $t->string('option_key')->unique();
                $t->text('option_value')->nullable();
                $t->timestamps();
            });
        }
    }

    private function svc(): FreeOnboardingService
    {
        return app(FreeOnboardingService::class);
    }

    private function makeLead(array $attrs = [], array $companyAttrs = []): Lead
    {
        $company = Company::create(array_merge([
            'company_name' => 'Acme Apartments',
            'email'        => 'owner@acme.test',
            'phone'        => '0700000000',
        ], $companyAttrs));

        return Lead::create(array_merge([
            'company_id'  => $company->id,
            'affiliate_id' => 1,
            'contact_person_name' => 'Jane',
            'status'      => 'active',
        ], $attrs));
    }

    private function affiliateUser(): User
    {
        return User::create(['first_name' => 'Aff', 'last_name' => 'One', 'email' => 'aff@test.test']);
    }

    public function test_invite_state_is_none_until_sent(): void
    {
        $lead = $this->makeLead();
        $this->assertSame('none', $this->svc()->inviteState($lead));
    }

    public function test_create_invite_sets_overlay_columns_logs_and_dispatches_mail(): void
    {
        $lead = $this->makeLead();

        $res = $this->svc()->createInvite($lead, $this->affiliateUser());

        $this->assertTrue($res['success']);
        $lead->refresh();
        $this->assertNotNull($lead->free_invite_token);
        $this->assertSame('owner@acme.test', $lead->free_invite_email);
        $this->assertSame('pending', $this->svc()->inviteState($lead));
        $this->assertSame(1, (int) $lead->free_invite_count);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'free_invite_sent']);
        Bus::assertDispatched(SendFreeInviteMail::class);
    }

    public function test_cannot_invite_without_a_valid_email(): void
    {
        $lead = $this->makeLead([], ['email' => null]);
        $gate = $this->svc()->canInvite($lead);
        $this->assertFalse($gate['ok']);

        $res = $this->svc()->createInvite($lead, $this->affiliateUser());
        $this->assertFalse($res['success']);
        Bus::assertNotDispatched(SendFreeInviteMail::class);
    }

    public function test_cannot_invite_when_an_account_already_exists(): void
    {
        User::create(['first_name' => 'Existing', 'email' => 'owner@acme.test']);
        $lead = $this->makeLead();

        $gate = $this->svc()->canInvite($lead);
        $this->assertFalse($gate['ok']);
        $this->assertStringContainsString('already has', $gate['reason']);
    }

    public function test_trial_and_closed_statuses_block_invites(): void
    {
        foreach (['pending_conversion', 'trial', 'converted', 'rejected', 'lost'] as $status) {
            $lead = $this->makeLead(['status' => $status], ['email' => "o+{$status}@acme.test"]);
            $this->assertFalse($this->svc()->canInvite($lead)['ok'], "status {$status} should block");
        }
    }

    public function test_expired_invite_state_is_derived_from_the_expiry(): void
    {
        $lead = $this->makeLead([
            'free_invite_token'      => 'tok-expired',
            'free_invite_email'      => 'owner@acme.test',
            'free_invite_sent_at'    => now()->subDays(10),
            'free_invite_expires_at' => now()->subDays(3),
        ]);
        $this->assertSame('expired', $this->svc()->inviteState($lead));
    }

    public function test_pending_cap_blocks_new_invites_but_not_a_resend(): void
    {
        setOption('affiliate_free_invite_max_pending', 2);
        $aff = $this->affiliateUser();

        // Two outstanding pending invites for this affiliate hit the cap.
        for ($i = 0; $i < 2; $i++) {
            Lead::create([
                'company_id' => Company::create(['company_name' => "C{$i}", 'email' => "c{$i}@t.test"])->id,
                'affiliate_id' => $aff->id, 'status' => 'active',
                'free_invite_token' => "t{$i}", 'free_invite_email' => "c{$i}@t.test",
                'free_invite_sent_at' => now(), 'free_invite_expires_at' => now()->addDays(7),
            ]);
        }
        $this->assertSame(2, $this->svc()->pendingCountForAffiliate($aff->id));

        // A brand-new prospect is blocked by the cap.
        $fresh = $this->makeLead([], ['email' => 'new@acme.test']);
        $fresh->affiliate_id = $aff->id;
        $fresh->save();
        $this->assertFalse($this->svc()->createInvite($fresh, $aff)['success']);

        // Re-sending one of the already-pending invites is allowed (not a new prospect).
        $live = Lead::where('free_invite_token', 't0')->first();
        $this->assertTrue($this->svc()->createInvite($live, $aff)['success']);
    }

    public function test_accept_rejects_invalid_expired_and_already_accepted(): void
    {
        $this->assertFalse($this->svc()->acceptInvite('nope', 'password123')['success']);

        $expired = $this->makeLead([
            'free_invite_token' => 'exp', 'free_invite_email' => 'o@acme.test',
            'free_invite_sent_at' => now()->subDays(10), 'free_invite_expires_at' => now()->subDay(),
        ]);
        $this->assertFalse($this->svc()->acceptInvite('exp', 'password123')['success']);

        $accepted = $this->makeLead([
            'free_invite_token' => 'acc', 'free_invite_email' => 'o2@acme.test',
            'free_invite_sent_at' => now()->subDay(), 'free_invite_expires_at' => now()->addDays(6),
            'free_invite_accepted_at' => now(),
        ]);
        $this->assertFalse($this->svc()->acceptInvite('acc', 'password123')['success']);
    }
}
