<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bounds the affiliate activation nudge: an email reminder to a certified affiliate who hasn't added
 * a single lead yet (the "trained and sitting" churn gap). Tracks how many nudges have gone out and
 * when, so it never spams — capped at a couple, spaced out, and it stops the moment they add a lead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            if (! Schema::hasColumn('affiliates', 'activation_nudge_count')) {
                $table->unsignedTinyInteger('activation_nudge_count')->default(0)->after('academy_certified_at');
            }
            if (! Schema::hasColumn('affiliates', 'activation_nudged_at')) {
                $table->timestamp('activation_nudged_at')->nullable()->after('activation_nudge_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            foreach (['activation_nudge_count', 'activation_nudged_at'] as $col) {
                if (Schema::hasColumn('affiliates', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
