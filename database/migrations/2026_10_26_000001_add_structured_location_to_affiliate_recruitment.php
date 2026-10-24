<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Structured location for the affiliate recruitment pipeline.
 *
 * Replaces the free-text location/coverage_area on applications with country + county + town
 * (sourced from config('centresidence.markets'), so new markets never touch the form), and carries
 * the same three onto the affiliate row at approval — which powers the admin "where are our
 * affiliates" coverage cards and surfaces the counties we're NOT yet represented in.
 * Column-guarded so a re-run on a shared host is safe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('affiliate_applications', 'country')) {
                $table->string('country')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('affiliate_applications', 'county')) {
                $table->string('county')->nullable()->after('country');
            }
            if (! Schema::hasColumn('affiliate_applications', 'town')) {
                $table->string('town')->nullable()->after('county');
            }
        });
        // Drop the superseded free-text fields (no production data yet — batch not mailed).
        Schema::table('affiliate_applications', function (Blueprint $table) {
            foreach (['coverage_area', 'location'] as $col) {
                if (Schema::hasColumn('affiliate_applications', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('affiliates', function (Blueprint $table) {
            if (! Schema::hasColumn('affiliates', 'country')) {
                $table->string('country')->nullable()->after('referral_code');
            }
            if (! Schema::hasColumn('affiliates', 'county')) {
                $table->string('county')->nullable()->after('country');
            }
            if (! Schema::hasColumn('affiliates', 'town')) {
                $table->string('town')->nullable()->after('county');
            }
        });
        // Index the pair we group the coverage cards by.
        Schema::table('affiliates', function (Blueprint $table) {
            $table->index(['country', 'county'], 'affiliates_country_county_index');
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_applications', function (Blueprint $table) {
            foreach (['country', 'county', 'town'] as $col) {
                if (Schema::hasColumn('affiliate_applications', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (! Schema::hasColumn('affiliate_applications', 'location')) {
                $table->string('location')->nullable();
            }
            if (! Schema::hasColumn('affiliate_applications', 'coverage_area')) {
                $table->string('coverage_area')->nullable();
            }
        });

        Schema::table('affiliates', function (Blueprint $table) {
            if (Schema::hasColumn('affiliates', 'country')) {
                $table->dropIndex('affiliates_country_county_index');
            }
            foreach (['country', 'county', 'town'] as $col) {
                if (Schema::hasColumn('affiliates', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
