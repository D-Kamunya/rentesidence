<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Closes the dispute loop both ways: the tenant can ACKNOWLEDGE the admin's reply (receipt) or
 * push back with a follow-up (`tenant_reply`, which reopens it), and the admin can flag that
 * they've asked the relevant owner(s) to reconcile off-system payments (`owner_notified_at`).
 * Guarded/self-healing for the shared host.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_credit_disputes') && ! Schema::hasColumn('tenant_credit_disputes', 'tenant_reply')) {
            Schema::table('tenant_credit_disputes', function (Blueprint $table) {
                $table->text('tenant_reply')->nullable()->after('resolved_at');
                $table->timestamp('tenant_ack_at')->nullable()->after('tenant_reply');   // tenant confirmed the reply helped
                $table->timestamp('owner_notified_at')->nullable()->after('tenant_ack_at'); // admin asked owner(s) to reconcile
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tenant_credit_disputes') && Schema::hasColumn('tenant_credit_disputes', 'tenant_reply')) {
            Schema::table('tenant_credit_disputes', fn (Blueprint $t) => $t->dropColumn(['tenant_reply', 'tenant_ack_at', 'owner_notified_at']));
        }
    }
};
