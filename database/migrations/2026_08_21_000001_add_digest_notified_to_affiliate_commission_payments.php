<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotency guard for the monthly affiliate commission digest — once a period's summary has
 * been emailed to the affiliate, we stamp it so a re-run never double-sends. Self-healing/guarded.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('affiliate_commission_payments')
            && ! Schema::hasColumn('affiliate_commission_payments', 'digest_notified_at')) {
            Schema::table('affiliate_commission_payments', function (Blueprint $table) {
                $table->timestamp('digest_notified_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('affiliate_commission_payments')
            && Schema::hasColumn('affiliate_commission_payments', 'digest_notified_at')) {
            Schema::table('affiliate_commission_payments', fn (Blueprint $t) => $t->dropColumn('digest_notified_at'));
        }
    }
};
