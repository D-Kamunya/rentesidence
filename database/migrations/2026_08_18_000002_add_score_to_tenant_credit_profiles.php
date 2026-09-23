<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The compound rental SCORE, computed from the objective payment-behaviour metrics. Stored on
 * the profile (queryable for screening + loan-suitability), versioned and explainable via a
 * factor breakdown. Behaviour-weighted; thin files regress to the mean so a one-invoice tenant
 * is never over-branded.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_credit_profiles')) {
            return;
        }
        Schema::table('tenant_credit_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_credit_profiles', 'score')) {
                $table->decimal('score', 5, 2)->nullable()->after('avg_days_late');     // 0–100 (null = unrated)
                $table->string('score_band', 24)->nullable()->after('score');           // excellent|good|fair|poor|high_risk|unrated
                $table->string('score_grade', 2)->nullable()->after('score_band');       // A–E
                $table->string('score_version', 12)->nullable()->after('score_grade');
                $table->boolean('is_thin_file')->default(true)->after('score_version');   // limited history → provisional
                $table->json('score_factors')->nullable()->after('is_thin_file');         // explainability breakdown
                $table->index('score_band');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('tenant_credit_profiles') && Schema::hasColumn('tenant_credit_profiles', 'score')) {
            Schema::table('tenant_credit_profiles', function (Blueprint $table) {
                $table->dropColumn(['score', 'score_band', 'score_grade', 'score_version', 'is_thin_file', 'score_factors']);
            });
        }
    }
};
