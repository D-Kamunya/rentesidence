<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A tenant-facing RESOLUTION reply on a dispute. `admin_note` stays the INTERNAL note; this is
 * the message the tenant sees when their dispute is answered — so the fairness loop is closed
 * visibly, not silently. Self-healing/guarded for the shared host.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_credit_disputes') && ! Schema::hasColumn('tenant_credit_disputes', 'resolution')) {
            Schema::table('tenant_credit_disputes', function (Blueprint $table) {
                $table->text('resolution')->nullable()->after('admin_note');
                $table->timestamp('resolved_at')->nullable()->after('resolution');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tenant_credit_disputes') && Schema::hasColumn('tenant_credit_disputes', 'resolution')) {
            Schema::table('tenant_credit_disputes', fn (Blueprint $t) => $t->dropColumn(['resolution', 'resolved_at']));
        }
    }
};
