<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Finding #5 — the period summary is a DERIVED aggregate that should have exactly
 * one row per (affiliate, month, year). The old code appended a fresh snapshot on
 * every commission event, so correctness depended on every reader remembering to
 * pick MAX(id) per period. This collapses the history to the latest snapshot per
 * period (which is the one readers already used) and adds a unique index so the
 * one-row invariant is enforced by the database, letting the service upsert safely.
 */
return new class extends Migration
{
    public function up()
    {
        // 1. Dedupe: for each (affiliate, month, year) keep the latest row (MAX id)
        //    — the most complete cumulative snapshot — and drop the earlier ones.
        //    Done in PHP so it is portable across drivers.
        $groups = DB::table('affiliate_commission_payments')
            ->selectRaw('affiliate_id, period_month, period_year, MAX(id) as keep_id, COUNT(*) as cnt')
            ->groupBy('affiliate_id', 'period_month', 'period_year')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($groups as $g) {
            DB::table('affiliate_commission_payments')
                ->where('affiliate_id', $g->affiliate_id)
                ->where('period_month', $g->period_month)
                ->where('period_year', $g->period_year)
                ->where('id', '<>', $g->keep_id)
                ->delete();
        }

        // 2. Enforce one-row-per-period at the DB level.
        Schema::table('affiliate_commission_payments', function (Blueprint $table) {
            $table->unique(['affiliate_id', 'period_month', 'period_year'], 'acp_affiliate_period_unique');
        });
    }

    public function down()
    {
        Schema::table('affiliate_commission_payments', function (Blueprint $table) {
            $table->dropUnique('acp_affiliate_period_unique');
        });
    }
};
