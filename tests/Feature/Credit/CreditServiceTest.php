<?php

namespace Tests\Feature\Credit;

use App\Models\OwnerCreditTransaction;
use App\Services\Credit\CreditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Money-invariant tests for the unified prepaid-credit rail. Runs against an isolated
 * in-memory sqlite connection with just the two tables the rail touches (owners +
 * owner_credit_transactions) — never the real database. Exercises both a plain bucket
 * (agreement) and a pooled bucket (sms) using the real config/credits.php registry.
 */
class CreditServiceTest extends TestCase
{
    private const UID = 4242;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.credit_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        config(['database.default' => 'credit_sqlite']);
        DB::purge('credit_sqlite');

        Schema::create('owners', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->unsignedInteger('sms_credits')->default(0);
            $t->unsignedInteger('sms_granted_credits')->default(0);
            $t->unsignedInteger('sms_purchased_credits')->default(0);
            $t->unsignedInteger('agreement_credits')->default(0);
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('owner_credit_transactions', function ($t) {
            $t->id();
            $t->string('bucket', 32);
            $t->unsignedBigInteger('owner_user_id');
            $t->string('type', 24);
            $t->unsignedInteger('quantity');
            $t->decimal('amount_paid', 10, 2)->nullable();
            $t->unsignedInteger('balance_before');
            $t->unsignedInteger('balance_after');
            $t->string('reference')->nullable();
            $t->string('payment_id')->nullable();
            $t->string('description')->nullable();
            $t->string('status', 12)->default('success');
            $t->timestamps();
        });

        DB::table('owners')->insert([
            'user_id' => self::UID, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('owner_credit_transactions');
        Schema::dropIfExists('owners');
        parent::tearDown();
    }

    /** A plain (non-pooled) bucket: add, deduct, and a ledger row per movement. */
    public function test_agreement_bucket_add_and_deduct(): void
    {
        $this->assertSame(0, CreditService::balance('agreement', self::UID));

        $this->assertSame(5, CreditService::addCredits('agreement', self::UID, 5, ['amount_paid' => 250]));
        $this->assertSame(5, CreditService::balance('agreement', self::UID));

        $this->assertTrue(CreditService::deductOne('agreement', self::UID));
        $this->assertSame(4, CreditService::balance('agreement', self::UID));

        // One ledger row per movement (1 purchase + 1 deduct).
        $this->assertSame(2, OwnerCreditTransaction::where('bucket', 'agreement')->count());
    }

    /** deductOne must refuse (and not go negative) on an empty balance. */
    public function test_deduct_refused_when_empty(): void
    {
        $this->assertFalse(CreditService::deductOne('agreement', self::UID));
        $this->assertSame(0, CreditService::balance('agreement', self::UID));
        // A refused deduct writes no ledger row.
        $this->assertSame(0, OwnerCreditTransaction::where('bucket', 'agreement')->count());
    }

    /** The core money guarantee: a re-fired top-up (callback + verify racing) credits ONCE. */
    public function test_pending_topup_is_idempotent(): void
    {
        $pending = CreditService::openPendingPurchase('agreement', self::UID, 3, 150, 'pending');
        $this->assertSame(0, CreditService::balance('agreement', self::UID)); // pending doesn't credit

        $opts = ['existing_transaction_id' => $pending->id, 'amount_paid' => 150];
        CreditService::addCredits('agreement', self::UID, 3, $opts); // callback
        CreditService::addCredits('agreement', self::UID, 3, $opts); // verify fallback re-fires
        CreditService::addCredits('agreement', self::UID, 3, $opts); // and again

        $this->assertSame(3, CreditService::balance('agreement', self::UID)); // credited exactly once
        $this->assertSame('success', $pending->fresh()->status);
    }

    /** Pooled bucket: purchases land in the purchased pool; deducts drain granted first. */
    public function test_sms_pools_granted_drained_before_purchased(): void
    {
        CreditService::resetGrantedAllowance('sms', self::UID, 10, 'monthly grant');
        CreditService::addCredits('sms', self::UID, 4, ['amount_paid' => 4]);

        $bd = CreditService::breakdown('sms', self::UID);
        $this->assertSame(10, $bd['granted']);
        $this->assertSame(4, $bd['purchased']);
        $this->assertSame(14, $bd['total']);

        // Drain the 10 granted — purchased must stay untouched.
        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue(CreditService::deductOne('sms', self::UID));
        }
        $bd = CreditService::breakdown('sms', self::UID);
        $this->assertSame(0, $bd['granted']);
        $this->assertSame(4, $bd['purchased']);

        // The 11th deduct now eats into purchased.
        CreditService::deductOne('sms', self::UID);
        $this->assertSame(3, CreditService::breakdown('sms', self::UID)['purchased']);
    }

    /** Resetting the granted allowance never touches purchased (owned) credits. */
    public function test_granted_reset_preserves_purchased(): void
    {
        CreditService::addCredits('sms', self::UID, 7, ['amount_paid' => 7]); // owned
        CreditService::resetGrantedAllowance('sms', self::UID, 100, 'period 1');
        CreditService::resetGrantedAllowance('sms', self::UID, 20, 'period 2'); // no rollover

        $bd = CreditService::breakdown('sms', self::UID);
        $this->assertSame(20, $bd['granted']);      // reset, not accumulated
        $this->assertSame(7, $bd['purchased']);     // owned credits survive
        $this->assertSame(27, $bd['total']);
    }

    public function test_unknown_bucket_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CreditService::balance('nonsense', self::UID);
    }

    public function test_reset_allowance_rejected_on_non_pooled_bucket(): void
    {
        $this->expectException(\LogicException::class);
        CreditService::resetGrantedAllowance('agreement', self::UID, 5, 'invalid');
    }
}
