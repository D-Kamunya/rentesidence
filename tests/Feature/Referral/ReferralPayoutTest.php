<?php

namespace Tests\Feature\Referral;

use App\Models\LandlordReferral;
use App\Models\ReferralPayout;
use App\Models\User;
use App\Services\LandlordReferralService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The payout rail (batched, admin-initiated, reusing the B2C callback). Isolated in-memory
 * sqlite. Locks the money invariants: payable rewards are reserved against double-payment
 * when a payout opens, a successful reconcile marks them paid, a failed one releases them
 * back to payable, and nothing pays out below the minimum floor.
 */
class ReferralPayoutTest extends TestCase
{
    private LandlordReferralService $svc;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.rpay_sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        config(['database.default' => 'rpay_sqlite']);
        DB::purge('rpay_sqlite');

        config([
            'referrals.enabled' => true, 'referrals.cash_enabled' => true,
            'referrals.currency' => 'KES', 'referrals.min_payout' => 500,
        ]);

        Schema::create('users', function ($t) {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('contact_number')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('referral_payouts', function ($t) {
            $t->id();
            $t->unsignedBigInteger('referrer_user_id');
            $t->decimal('amount', 12, 2);
            $t->string('currency', 8)->nullable();
            $t->string('phone', 32)->nullable();
            $t->string('status', 12)->default('pending');
            $t->string('settlement_method', 12)->nullable();
            $t->string('mpesa_reference')->nullable();
            $t->string('transaction_id')->nullable();
            $t->timestamp('processed_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('landlord_referrals', function ($t) {
            $t->id();
            $t->unsignedBigInteger('referrer_user_id');
            $t->string('code', 32)->default('X');
            $t->string('status', 20)->default('confirmed');
            $t->unsignedBigInteger('lead_id')->nullable();
            $t->unsignedBigInteger('owner_id')->nullable();
            $t->unsignedBigInteger('payout_id')->nullable();
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

    /** Three confirmed, held-elapsed cash rewards (3×200 = 600, over the 500 floor). */
    private function seedPayable(int $userId, int $n = 3, float $each = 200): void
    {
        for ($i = 0; $i < $n; $i++) {
            LandlordReferral::create([
                'referrer_user_id' => $userId, 'code' => 'CODE', 'status' => LandlordReferral::STATUS_CONFIRMED,
                'owner_id' => 500 + $i, 'reward_type' => LandlordReferral::REWARD_CASH, 'reward_amount' => $each,
                'currency' => 'KES', 'held_until' => now()->subDay(), 'confirmed_at' => now()->subDays(31),
            ]);
        }
    }

    public function test_below_minimum_or_no_phone_opens_no_payout(): void
    {
        $tenant = User::create(['first_name' => 'Low', 'contact_number' => '254700000001']);
        LandlordReferral::create([
            'referrer_user_id' => $tenant->id, 'code' => 'C', 'status' => LandlordReferral::STATUS_CONFIRMED,
            'owner_id' => 1, 'reward_type' => LandlordReferral::REWARD_CASH, 'reward_amount' => 200,
            'currency' => 'KES', 'held_until' => now()->subDay(),
        ]);
        $this->assertNull($this->svc->openPayout($tenant->id), 'Below the 500 floor → no payout.');

        $noPhone = User::create(['first_name' => 'NoPhone']);
        $this->seedPayable($noPhone->id);
        $this->assertNull($this->svc->openPayout($noPhone->id), 'No registered phone → no payout.');
    }

    public function test_open_payout_reserves_referrals_and_removes_them_from_payable(): void
    {
        $tenant = User::create(['first_name' => 'Pay', 'contact_number' => '254711000111']);
        $this->seedPayable($tenant->id);

        $this->assertEquals(600.0, $this->svc->payableBalance($tenant->id));

        $payout = $this->svc->openPayout($tenant->id);
        $this->assertNotNull($payout);
        $this->assertEquals(600.0, (float) $payout->amount);
        $this->assertSame(ReferralPayout::STATUS_PENDING, $payout->status);
        // Reserved → excluded from a further payable balance (no double-pay).
        $this->assertEquals(0.0, $this->svc->payableBalance($tenant->id));
        $this->assertSame(3, LandlordReferral::where('payout_id', $payout->id)->count());
    }

    public function test_successful_reconcile_marks_referrals_paid(): void
    {
        $tenant = User::create(['first_name' => 'Ok', 'contact_number' => '254711000222']);
        $this->seedPayable($tenant->id);
        $payout = $this->svc->openPayout($tenant->id);
        $this->svc->markPayoutProcessing($payout, 'CONV-123');

        $this->svc->reconcilePayout($payout->fresh(), true, 'MPESA-XYZ');

        $this->assertSame(ReferralPayout::STATUS_PAID, $payout->fresh()->status);
        $this->assertSame('MPESA-XYZ', $payout->fresh()->transaction_id);
        $this->assertSame(3, LandlordReferral::where('payout_id', $payout->id)->where('status', LandlordReferral::STATUS_PAID)->count());
    }

    public function test_failed_reconcile_releases_referrals_back_to_payable(): void
    {
        $tenant = User::create(['first_name' => 'Fail', 'contact_number' => '254711000333']);
        $this->seedPayable($tenant->id);
        $payout = $this->svc->openPayout($tenant->id);
        $this->svc->markPayoutProcessing($payout, 'CONV-999');

        $this->svc->reconcilePayout($payout->fresh(), false, null, 'Insufficient funds');

        $this->assertSame(ReferralPayout::STATUS_FAILED, $payout->fresh()->status);
        // Released: back to payable, retryable.
        $this->assertEquals(600.0, $this->svc->payableBalance($tenant->id));
        $this->assertSame(0, LandlordReferral::where('payout_id', $payout->id)->count());

        // Idempotent: reconciling a non-processing payout does nothing.
        $this->svc->reconcilePayout($payout->fresh(), true, 'LATE');
        $this->assertSame(ReferralPayout::STATUS_FAILED, $payout->fresh()->status);
    }

    public function test_manual_settlement_pays_immediately(): void
    {
        $tenant = User::create(['first_name' => 'Man', 'contact_number' => '254711000444']);
        $this->seedPayable($tenant->id);
        $payout = $this->svc->openPayout($tenant->id);

        $this->svc->settlePayoutManually($payout, 'Paid by bank transfer');

        $this->assertSame(ReferralPayout::STATUS_PAID, $payout->fresh()->status);
        $this->assertSame('manual', $payout->fresh()->settlement_method);
        $this->assertSame(3, LandlordReferral::where('payout_id', $payout->id)->where('status', LandlordReferral::STATUS_PAID)->count());
    }

    public function test_eligible_list_surfaces_the_tenant(): void
    {
        $tenant = User::create(['first_name' => 'Eli', 'contact_number' => '254711000555']);
        $this->seedPayable($tenant->id);

        $eligible = $this->svc->tenantsEligibleForPayout();
        $this->assertCount(1, $eligible);
        $this->assertEquals(600.0, (float) $eligible->first()->payable);
        $this->assertEquals(3, (int) $eligible->first()->reward_count);
    }
}
