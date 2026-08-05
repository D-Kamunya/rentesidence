<?php

namespace Tests\Feature\Affiliate;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Independent in-memory SQLite harness for the affiliate money code.
 *
 * Boots its OWN sqlite connection and creates only the affiliate tables it needs
 * — so these tests run fast, isolated, and disposable, and NEVER depend on the
 * local MySQL being up or touch the real database. (Same pattern the Centresidence
 * simulation sandbox uses.) Not suffixed *Test so PHPUnit does not collect it.
 */
abstract class AffiliateDatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'affiliate_sqlite',
            'database.connections.affiliate_sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('affiliate_sqlite');
        DB::setDefaultConnection('affiliate_sqlite');

        // Teach sqlite MySQL's FIELD(value, …list) so queries that order by
        // FIELD(priority, 'high','medium','low') (as the app does) run in tests.
        DB::connection('affiliate_sqlite')->getPdo()->sqliteCreateFunction('FIELD', function ($value, ...$list) {
            $i = array_search($value, $list, false);
            return $i === false ? 0 : $i + 1;
        });

        $this->createTables();
    }

    private function createTables(): void
    {
        Schema::create('affiliates', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('referral_code')->nullable();
            $t->integer('status')->nullable();
            $t->timestamps();
        });

        Schema::create('affiliate_commission_payments', function ($t) {
            $t->id();
            $t->unsignedBigInteger('affiliate_id');
            $t->integer('period_month');
            $t->integer('period_year');
            $t->integer('total_new_clients')->default(0);
            $t->integer('total_recurring_clients')->default(0);
            $t->decimal('new_commissions_amount', 14, 2)->default(0);
            $t->decimal('recurring_commissions_amount', 14, 2)->default(0);
            $t->decimal('total_commission_payout', 14, 2)->default(0);
            $t->decimal('new_commission_payout', 14, 2)->default(0);
            $t->decimal('recurring_commission_payout', 14, 2)->default(0);
            $t->decimal('rent_commissions_amount', 14, 2)->default(0);
            $t->decimal('rent_commission_payout', 14, 2)->default(0);
            $t->decimal('marketplace_commissions_amount', 14, 2)->default(0);
            $t->decimal('marketplace_commission_payout', 14, 2)->default(0);
            $t->timestamps();
            // Mirror the prod one-row-per-period invariant (Finding #5).
            $t->unique(['affiliate_id', 'period_month', 'period_year'], 'acp_affiliate_period_unique');
        });

        // Source-of-truth commission rows that recalculatePeriodSummary aggregates —
        // now the generalised ledger (WP-B): product + external_ref idempotency.
        Schema::create('affiliate_commissions', function ($t) {
            $t->id();
            $t->unsignedBigInteger('affiliate_id');
            $t->unsignedBigInteger('owner_id')->nullable();
            $t->string('product')->default('property_sales');
            $t->string('source')->nullable();
            $t->string('external_ref')->nullable();
            $t->string('type')->nullable();
            $t->decimal('subscription_amount', 14, 2)->default(0);
            $t->unsignedBigInteger('subscription_id')->nullable();
            $t->unsignedBigInteger('subscription_payment_id')->nullable();
            $t->decimal('commission_amount', 14, 2)->default(0);
            $t->decimal('commission_rate', 8, 4)->default(0);
            $t->string('currency', 8)->default('KES');
            $t->string('cadence', 20)->nullable();
            $t->unsignedBigInteger('order_id')->nullable();
            $t->integer('period_month');
            $t->integer('period_year');
            $t->timestamps();
            $t->unique(['product', 'source', 'external_ref'], 'ac_product_source_ref_unique');
        });

        Schema::create('affiliate_withdrawals', function ($t) {
            $t->id();
            $t->unsignedBigInteger('affiliate_id');
            $t->decimal('amount', 14, 2);
            $t->integer('status');
            $t->string('phone')->nullable();
            $t->string('settlement_method')->nullable();
            $t->timestamp('processed_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }
}
