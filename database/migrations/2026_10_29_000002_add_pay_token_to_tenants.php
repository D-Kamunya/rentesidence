<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Scan-to-pay: a STABLE per-tenant token behind a printable QR the owner can pin on a unit. Unlike
 * the per-invoice payment_token (which expires each cycle), this one is durable — scanning it
 * resolves the tenant's CURRENT unpaid rent invoice at that moment and hands off to the existing
 * public instant-pay flow (STK → reconcile → receipt). Backfills existing tenants.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'pay_token')) {
                $table->string('pay_token', 48)->nullable()->unique()->after('status');
            }
        });

        // Backfill a unique token for every tenant that doesn't have one yet.
        DB::table('tenants')->whereNull('pay_token')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                DB::table('tenants')->where('id', $row->id)->update(['pay_token' => Str::random(40)]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'pay_token')) {
                $table->dropColumn('pay_token');
            }
        });
    }
};
