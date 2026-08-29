<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Splits an owner's SMS balance into two pools so package grants can reset each
 * renewal WITHOUT ever wiping credits the owner PAID for:
 *   - sms_granted_credits   → the current period's package allowance (RESET on renewal, no rollover)
 *   - sms_purchased_credits → top-ups / refunds / admin gifts (never expire)
 * `sms_credits` stays as the effective total (granted + purchased) that the rest of
 * the app already reads. Deductions draw from the granted pool first.
 *
 * Backfill: existing balances are treated as PURCHASED (non-expiring), so no owner
 * loses a single credit at rollout; the reset behaviour applies from the next grant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->unsignedInteger('sms_granted_credits')->default(0)->after('sms_credits');
            $table->unsignedInteger('sms_purchased_credits')->default(0)->after('sms_granted_credits');
        });

        DB::table('owners')->update([
            'sms_purchased_credits' => DB::raw('sms_credits'),
            'sms_granted_credits'   => 0,
        ]);
    }

    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn(['sms_granted_credits', 'sms_purchased_credits']);
        });
    }
};
