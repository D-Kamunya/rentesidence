<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks whether an owner has received the one-time "stuck in setup" follow-up (a second touch after
 * the welcome), so it's sent once and the command can safely re-run / backfill without duplicating.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            if (! Schema::hasColumn('owners', 'setup_followup_at')) {
                $table->timestamp('setup_followup_at')->nullable()->after('welcomed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            if (Schema::hasColumn('owners', 'setup_followup_at')) {
                $table->dropColumn('setup_followup_at');
            }
        });
    }
};
