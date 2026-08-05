<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Affiliate OS WP-B — generalise affiliate_commissions into the commission-event
 * ledger (docs/affiliate-os-design.md §4/§6). Adds:
 *  - external_ref: the idempotency key (was source-specific: subscription used
 *    subscription_payment_id, rent/marketplace used order_id) — unified so a single
 *    unique index (product, source, external_ref) enforces no-double-credit across
 *    ALL sources. This CLOSES a real gap: subscription had no idempotency guard.
 *  - currency: settlement currency (KES today); never sum currencies naively.
 *  - cadence: one_time | recurring (descriptive; window logic unchanged).
 * Backfilled from existing columns; erroneous double-credits (same product+source+
 * external_ref) are deduped keeping the earliest row before the unique index.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->string('external_ref')->nullable()->after('order_id');
            $table->string('currency', 8)->default('KES')->after('commission_amount');
            $table->string('cadence', 20)->nullable()->after('currency');
        });

        // Backfill the idempotency key + cadence from the source-specific columns.
        DB::table('affiliate_commissions')->where('source', 'subscription')->whereNull('external_ref')
            ->update(['external_ref' => DB::raw('subscription_payment_id'), 'cadence' => 'recurring']);
        DB::table('affiliate_commissions')->where('source', 'rent')->whereNull('external_ref')
            ->update(['external_ref' => DB::raw('order_id'), 'cadence' => 'recurring']);
        DB::table('affiliate_commissions')->where('source', 'marketplace')->whereNull('external_ref')
            ->update(['external_ref' => DB::raw('order_id'), 'cadence' => 'one_time']);

        // Any stragglers with no source column populated → a unique legacy ref so the
        // index can be created without collision.
        DB::table('affiliate_commissions')->whereNull('external_ref')
            ->update(['external_ref' => DB::raw("CONCAT('legacy-', id)")]);
        DB::table('affiliate_commissions')->whereNull('currency')->update(['currency' => 'KES']);
        DB::table('affiliate_commissions')->whereNull('cadence')->update(['cadence' => 'recurring']);

        // Dedupe erroneous double-credits on the idempotency key (keep earliest).
        $groups = DB::table('affiliate_commissions')
            ->selectRaw('product, source, external_ref, MIN(id) as keep_id, COUNT(*) as cnt')
            ->groupBy('product', 'source', 'external_ref')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($groups as $g) {
            DB::table('affiliate_commissions')
                ->where('product', $g->product)
                ->where('source', $g->source)
                ->where('external_ref', $g->external_ref)
                ->where('id', '<>', $g->keep_id)
                ->delete();
        }

        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->unique(['product', 'source', 'external_ref'], 'ac_product_source_ref_unique');
        });
    }

    public function down()
    {
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->dropUnique('ac_product_source_ref_unique');
            $table->dropColumn(['external_ref', 'currency', 'cadence']);
        });
    }
};
