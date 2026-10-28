<?php

namespace Tests\Feature\Owner;

use App\Models\Owner;
use App\Services\Credit\CreditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The SMS credit-pool money invariants the Transaction monthly cushion depends on:
 *   - a monthly GRANT resets (no rollover) — unused grant never stockpiles;
 *   - PURCHASED credits are never touched by a reset — bought credits never expire;
 *   - spending draws the expiring GRANT first, so a later reset can't eat paid-for credits.
 * Runs on an isolated in-memory SQLite harness (no live MySQL). This is the coverage the user is
 * relying on since real SMS/credit flows can't be exercised by hand here.
 */
class SmsCreditPoolsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sms_credit_sqlite',
            'database.connections.sms_credit_sqlite' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('sms_credit_sqlite');
        DB::setDefaultConnection('sms_credit_sqlite');

        Schema::create('owners', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->integer('status')->default(1);
            $t->unsignedInteger('sms_credits')->default(0);
            $t->unsignedInteger('sms_granted_credits')->default(0);
            $t->unsignedInteger('sms_purchased_credits')->default(0);
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('owner_credit_transactions', function ($t) {
            $t->id();
            $t->string('bucket')->nullable();
            $t->unsignedBigInteger('owner_user_id');
            $t->string('type')->nullable();
            $t->integer('quantity')->default(0);
            $t->decimal('amount_paid', 14, 2)->nullable();
            $t->integer('balance_before')->nullable();
            $t->integer('balance_after')->nullable();
            $t->string('reference')->nullable();
            $t->string('description')->nullable();
            $t->unsignedBigInteger('payment_id')->nullable();
            $t->string('status')->nullable();
            $t->timestamps();
        });
    }

    private function owner(int $userId = 501): Owner
    {
        return Owner::create(['user_id' => $userId, 'status' => 1]);
    }

    public function test_purchase_lands_in_the_purchased_pool(): void
    {
        $o = $this->owner();
        CreditService::addCredits('sms', $o->user_id, 40, ['type' => 'purchase']);

        $b = CreditService::breakdown('sms', $o->user_id);
        $this->assertSame(0, $b['granted']);
        $this->assertSame(40, $b['purchased']);
        $this->assertSame(40, $b['total']);
    }

    public function test_reset_sets_the_grant_and_leaves_purchased_intact(): void
    {
        $o = $this->owner();
        CreditService::addCredits('sms', $o->user_id, 40, ['type' => 'purchase']);   // bought
        CreditService::resetGrantedAllowance('sms', $o->user_id, 100, 'cushion');     // monthly cushion

        $b = CreditService::breakdown('sms', $o->user_id);
        $this->assertSame(100, $b['granted']);   // cushion applied
        $this->assertSame(40, $b['purchased']);  // bought credits untouched
        $this->assertSame(140, $b['total']);
    }

    public function test_reset_does_not_roll_over_unused_grant(): void
    {
        $o = $this->owner();
        CreditService::resetGrantedAllowance('sms', $o->user_id, 100, 'month 1'); // grant 100, unused
        CreditService::resetGrantedAllowance('sms', $o->user_id, 100, 'month 2'); // reset — NOT 200

        $b = CreditService::breakdown('sms', $o->user_id);
        $this->assertSame(100, $b['granted']); // no stockpiling
        $this->assertSame(100, $b['total']);
    }

    public function test_spending_draws_grant_first_then_purchased_and_reset_restores_only_the_grant(): void
    {
        $o = $this->owner();
        CreditService::addCredits('sms', $o->user_id, 20, ['type' => 'purchase']); // 20 bought (never expire)
        CreditService::resetGrantedAllowance('sms', $o->user_id, 5, 'cushion');     // 5 granted
        // total 25. Spend 7 → drains the 5 grant first, then 2 of purchased.
        for ($i = 0; $i < 7; $i++) {
            $this->assertTrue(CreditService::deductOne('sms', $o->user_id));
        }
        $b = CreditService::breakdown('sms', $o->user_id);
        $this->assertSame(0, $b['granted']);
        $this->assertSame(18, $b['purchased']); // only 2 of the bought credits consumed
        $this->assertSame(18, $b['total']);

        // Next month's cushion restores the grant; the 18 bought credits remain.
        CreditService::resetGrantedAllowance('sms', $o->user_id, 100, 'month 2');
        $b = CreditService::breakdown('sms', $o->user_id);
        $this->assertSame(100, $b['granted']);
        $this->assertSame(18, $b['purchased']);
        $this->assertSame(118, $b['total']);
    }
}
