<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invite progress counters on a tenant import. Invites are sent by separate queued jobs, so
 * the progress UI reads these to show "invites sent" alongside "rows processed" — the run
 * only reads as fully complete once both rows and invites have drained.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_imports')) {
            return;
        }
        Schema::table('tenant_imports', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_imports', 'invites_queued')) {
                $table->unsignedInteger('invites_queued')->default(0)->after('skipped_count');
                $table->unsignedInteger('invites_sent')->default(0)->after('invites_queued');
                $table->unsignedInteger('invites_failed')->default(0)->after('invites_sent');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('tenant_imports') && Schema::hasColumn('tenant_imports', 'invites_queued')) {
            Schema::table('tenant_imports', function (Blueprint $table) {
                $table->dropColumn(['invites_queued', 'invites_sent', 'invites_failed']);
            });
        }
    }
};
