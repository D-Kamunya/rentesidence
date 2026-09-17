<?php

namespace Tests\Feature\Referral;

use App\Models\LandlordReferral;
use App\Models\Lead;
use App\Models\User;
use App\Services\LandlordReferralService;
use App\Services\LeadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The public invite intake path (Slice 2): an invited landlord's form creates a vetted
 * marketplace LEAD (never an owner account) and the referral ledger attaches to it.
 * Isolated in-memory sqlite with only the tables the flow touches.
 */
class ReferralIntakeTest extends TestCase
{
    private LeadService $leads;
    private LandlordReferralService $referrals;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.refi_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        config(['database.default' => 'refi_sqlite']);
        DB::purge('refi_sqlite');

        config(['referrals.enabled' => true, 'referrals.cash_enabled' => true, 'referrals.cash_amount' => 200, 'referrals.currency' => 'KES', 'referrals.hold_days' => 30]);

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

        Schema::create('companies', function ($t) {
            $t->id();
            $t->string('company_name')->nullable();
            $t->string('normalized_name')->nullable();
            $t->string('country')->nullable();
            $t->string('city')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('website')->nullable();
            $t->string('property_type')->nullable();
            $t->integer('estimated_units')->nullable();
            $t->string('sales_status')->nullable();
            $t->timestamps();
        });

        Schema::create('leads', function ($t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->nullable();
            $t->unsignedBigInteger('affiliate_id')->nullable();
            $t->unsignedBigInteger('owner_id')->nullable();
            $t->string('contact_person_name')->nullable();
            $t->string('contact_person_role')->nullable();
            $t->string('temperature')->nullable();
            $t->string('status')->nullable();
            $t->string('source')->nullable();
            $t->string('marketplace_status')->nullable();
            $t->timestamp('marketplace_at')->nullable();
            $t->timestamp('ownership_expires_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('lead_activities', function ($t) {
            $t->id();
            $t->unsignedBigInteger('lead_id');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('type')->nullable();
            $t->text('description')->nullable();
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

        $this->leads = new LeadService();
        $this->referrals = new LandlordReferralService();
    }

    public function test_intake_creates_a_vetted_marketplace_lead(): void
    {
        $lead = $this->leads->createReferralMarketplaceLead([
            'contact_person_name' => 'Jane Wanjiru',
            'company_name'        => 'Wanjiru Apartments',
            'phone'               => '254700111000',
            'email'               => 'jane@example.com',
            'estimated_units'     => 12,
        ]);

        $this->assertSame('admin', $lead->source, 'Referral leads enter the marketplace/vetting pool.');
        $this->assertSame('marketplace', $lead->marketplace_status);
        $this->assertNull($lead->affiliate_id, 'No affiliate is assigned yet — it is claimable.');
        $this->assertSame('warm', $lead->temperature);
        $this->assertNull($lead->owner_id, 'Intake never creates an owner account.');
        $this->assertSame(1, Lead::count());
        $this->assertSame(1, DB::table('lead_activities')->where('lead_id', $lead->id)->count());
    }

    public function test_intake_reuses_an_existing_active_lead_for_the_same_company(): void
    {
        $first = $this->leads->createReferralMarketplaceLead(['company_name' => 'Kilimani Homes', 'phone' => '254700222000', 'contact_person_name' => 'A']);
        $second = $this->leads->createReferralMarketplaceLead(['company_name' => 'Kilimani Homes', 'phone' => '254700222000', 'contact_person_name' => 'A']);

        $this->assertSame($first->id, $second->id, 'A second invite for the same company reuses the lead.');
        $this->assertSame(1, Lead::count());
    }

    public function test_full_intake_attaches_referral_and_confirms_on_real_money(): void
    {
        // Tenant has a code and logged a pending invite for this landlord.
        $tenant = User::create(['first_name' => 'Tess', 'role' => USER_ROLE_TENANT, 'contact_number' => '254700999000']);
        $code = $this->referrals->codeForTenant($tenant->id);
        $this->referrals->startInvite($tenant, ['name' => 'Larry Landlord', 'phone' => '254711333222']);

        // Landlord submits the public intake form → lead created, referral attaches.
        $lead = $this->leads->createReferralMarketplaceLead([
            'company_name' => 'Larry Estates', 'phone' => '254711333222', 'contact_person_name' => 'Larry Landlord',
        ]);
        $ref = $this->referrals->attachLead($code, $lead, ['name' => 'Larry Landlord', 'phone' => '254711333222']);

        $this->assertSame(LandlordReferral::STATUS_LEAD_CREATED, $ref->status);
        $this->assertSame($lead->id, $ref->lead_id);
        $this->assertSame(1, LandlordReferral::where('referrer_user_id', $tenant->id)->count(), 'Reused the pending invite.');

        // Admin later converts the lead → owner; real money confirms the reward.
        $lead->update(['owner_id' => 77, 'status' => 'converted']);
        $confirmed = $this->referrals->confirmForOwner(77, 'first_subscription');

        $this->assertSame(LandlordReferral::STATUS_CONFIRMED, $confirmed->status);
        $this->assertSame(LandlordReferral::REWARD_CASH, $confirmed->reward_type);
        $this->assertEquals(200.0, (float) $confirmed->reward_amount);
    }
}
