<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Turns the one-off "stuck in setup" follow-up into an ongoing, stage-aware cadence:
 *  - setup_followup_count: how many follow-ups we've sent for the CURRENT sticking point (bounds it).
 *  - setup_followup_step:  the step key we last nudged about — when the owner advances past it, the
 *                          cadence resets so the next sticking point gets its own fresh sequence.
 * setup_followup_at (already present) is the "last follow-up sent at" timestamp used for spacing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            if (! Schema::hasColumn('owners', 'setup_followup_count')) {
                $table->unsignedInteger('setup_followup_count')->default(0)->after('setup_followup_at');
            }
            if (! Schema::hasColumn('owners', 'setup_followup_step')) {
                $table->string('setup_followup_step', 40)->nullable()->after('setup_followup_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            foreach (['setup_followup_count', 'setup_followup_step'] as $col) {
                if (Schema::hasColumn('owners', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
