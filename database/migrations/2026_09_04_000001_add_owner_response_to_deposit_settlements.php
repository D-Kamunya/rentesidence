<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Owner response to a reported ("disputed") deposit settlement (Model A, no adjudication). The owner
 * RESPONDS (e.g. "re-sent via M-Pesa, code X") — they never self-resolve; resolution still routes
 * back through the tenant confirming receipt. owner_responded_at clears the OWNER's action nudge
 * (their part is done) and moves the item into the tenant's court.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('deposit_settlements') && !Schema::hasColumn('deposit_settlements', 'owner_responded_at')) {
            Schema::table('deposit_settlements', function (Blueprint $table) {
                $table->text('owner_response_note')->nullable()->after('tenant_responded_at');
                $table->timestamp('owner_responded_at')->nullable()->after('owner_response_note');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('deposit_settlements') && Schema::hasColumn('deposit_settlements', 'owner_responded_at')) {
            Schema::table('deposit_settlements', function (Blueprint $table) {
                $table->dropColumn(['owner_response_note', 'owner_responded_at']);
            });
        }
    }
};
