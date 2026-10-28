<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bounds the OWNER activation nudge: a reminder to an owner who signed up but hasn't reached a
 * meaningful activation milestone (no property, or a property but no tenant yet). An inactive owner
 * generates no revenue — and no affiliate commission — so moving them to their first tenant/rent is
 * where value starts. Mirrors the affiliate activation-nudge counters.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            if (! Schema::hasColumn('owners', 'activation_nudge_count')) {
                $table->unsignedTinyInteger('activation_nudge_count')->default(0)->after('status');
            }
            if (! Schema::hasColumn('owners', 'activation_nudged_at')) {
                $table->timestamp('activation_nudged_at')->nullable()->after('activation_nudge_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            foreach (['activation_nudge_count', 'activation_nudged_at'] as $col) {
                if (Schema::hasColumn('owners', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
