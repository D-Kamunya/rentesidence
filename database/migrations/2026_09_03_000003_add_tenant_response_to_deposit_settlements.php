<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant attestation (Phase 4, Slice 2) — the tenant's confirm-receipt / dispute response to a
 * recorded deposit settlement. A mutual record + dispute trail (Model A), not adjudication.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('deposit_settlements') && !Schema::hasColumn('deposit_settlements', 'tenant_responded_at')) {
            Schema::table('deposit_settlements', function (Blueprint $table) {
                $table->text('tenant_response_note')->nullable()->after('notes'); // reason on dispute
                $table->timestamp('tenant_responded_at')->nullable()->after('tenant_response_note');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('deposit_settlements') && Schema::hasColumn('deposit_settlements', 'tenant_responded_at')) {
            Schema::table('deposit_settlements', function (Blueprint $table) {
                $table->dropColumn(['tenant_response_note', 'tenant_responded_at']);
            });
        }
    }
};
