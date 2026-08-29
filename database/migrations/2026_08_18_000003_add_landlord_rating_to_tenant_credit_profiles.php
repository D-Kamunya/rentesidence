<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aggregated landlord ratings — the AVERAGE of the numeric rent/discipline ratings across all of
 * a person's rated tenancies (1–5). A SECONDARY, low-weighted signal in the compound score; the
 * per-landlord ratings themselves are never surfaced as named declarations (no gossip board).
 * Replaces the old broken per-owner rating lookup.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_credit_profiles')) {
            return;
        }
        Schema::table('tenant_credit_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_credit_profiles', 'landlord_rating_avg')) {
                $table->decimal('landlord_rating_avg', 3, 2)->nullable()->after('owners_count'); // 1–5, null = unrated
                $table->unsignedInteger('ratings_count')->default(0)->after('landlord_rating_avg');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('tenant_credit_profiles') && Schema::hasColumn('tenant_credit_profiles', 'landlord_rating_avg')) {
            Schema::table('tenant_credit_profiles', function (Blueprint $table) {
                $table->dropColumn(['landlord_rating_avg', 'ratings_count']);
            });
        }
    }
};
