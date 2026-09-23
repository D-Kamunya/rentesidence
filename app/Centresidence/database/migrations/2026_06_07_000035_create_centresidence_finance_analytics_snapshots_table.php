<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `finance_analytics_snapshots` — daily portfolio snapshot for dashboards and
 * trend analysis (handbook §9.9). One row per day (idempotent upsert).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_analytics_snapshots', function (Blueprint $table) {
            $table->id();

            $table->date('snapshot_date')->unique();

            $table->integer('total_active_facilities')->default(0);
            $table->decimal('total_outstanding_principal', 16, 2)->default(0);
            $table->decimal('total_outstanding_interest', 16, 2)->default(0);
            $table->decimal('total_outstanding_penalty', 16, 2)->default(0);

            $table->decimal('total_expected_monthly', 16, 2)->default(0);
            $table->decimal('total_collected_month', 16, 2)->default(0);
            $table->decimal('collection_rate', 5, 2)->default(0);

            $table->integer('facilities_in_default')->default(0);
            $table->decimal('default_rate', 5, 2)->default(0);

            $table->decimal('total_platform_fees_month', 16, 2)->default(0);
            $table->decimal('total_platform_fees_ytd', 16, 2)->default(0);
            $table->decimal('average_interest_rate', 5, 2)->default(0);

            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_analytics_snapshots');
    }
};
