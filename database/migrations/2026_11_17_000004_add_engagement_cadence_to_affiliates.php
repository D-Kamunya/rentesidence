<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Affiliate engagement cadence state — the sibling of the owner onboarding cadence:
 *  - welcomed_at: the one-time welcome (support thread) has fired.
 *  - engagement_followup_count: nudges sent for the CURRENT stuck stage (bounds it).
 *  - engagement_followup_step:  the stage last nudged — when the affiliate advances, it resets so the
 *                               next stage gets a fresh sequence (academy → first lead → convert).
 *  - engagement_followup_at:    last follow-up sent (spacing).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            if (! Schema::hasColumn('affiliates', 'welcomed_at')) {
                $table->timestamp('welcomed_at')->nullable()->after('academy_certified_at');
            }
            if (! Schema::hasColumn('affiliates', 'engagement_followup_at')) {
                $table->timestamp('engagement_followup_at')->nullable()->after('welcomed_at');
            }
            if (! Schema::hasColumn('affiliates', 'engagement_followup_count')) {
                $table->unsignedInteger('engagement_followup_count')->default(0)->after('engagement_followup_at');
            }
            if (! Schema::hasColumn('affiliates', 'engagement_followup_step')) {
                $table->string('engagement_followup_step', 40)->nullable()->after('engagement_followup_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            foreach (['welcomed_at', 'engagement_followup_at', 'engagement_followup_count', 'engagement_followup_step'] as $col) {
                if (Schema::hasColumn('affiliates', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
