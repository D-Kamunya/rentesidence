<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks whether an owner has received their in-app welcome message — so the welcome is sent
 * once on signup and the backfill command can safely cover existing owners without duplicating.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            if (! Schema::hasColumn('owners', 'welcomed_at')) {
                $table->timestamp('welcomed_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            if (Schema::hasColumn('owners', 'welcomed_at')) {
                $table->dropColumn('welcomed_at');
            }
        });
    }
};
