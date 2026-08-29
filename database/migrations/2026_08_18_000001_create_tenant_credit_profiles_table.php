<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The seed of the Global Tenant ID — one row per tenant PERSON (not per tenancy), keyed by a
 * canonical identity (normalised phone now; National ID / KRA PIN later). Holds the OBJECTIVE
 * payment-behaviour metrics aggregated from that person's invoices across ALL their tenancies
 * and owners. This is the backbone of tenant screening + future loan-suitability; the compound
 * SCORE is computed on top of these raw metrics in a later step.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_credit_profiles')) {
            return;
        }

        Schema::create('tenant_credit_profiles', function (Blueprint $table) {
            $table->id();

            // Identity — the person, resolved across owners/tenancies.
            $table->string('identity_key', 32)->unique();   // canonical key (normalised phone for now)
            $table->string('phone', 20)->nullable();
            $table->string('national_id', 32)->nullable()->index(); // future strong key
            $table->string('display_name')->nullable();       // latest known name (for display)

            // Reach of history (breadth signals).
            $table->unsignedInteger('tenancies_count')->default(0); // distinct tenancies (Tenant rows)
            $table->unsignedInteger('owners_count')->default(0);    // distinct landlords seen

            // Objective payment behaviour (the crown jewel).
            $table->unsignedInteger('invoices_total')->default(0);
            $table->unsignedInteger('invoices_paid')->default(0);
            $table->unsignedInteger('on_time_count')->default(0);   // paid on/before due date
            $table->unsignedInteger('late_count')->default(0);      // paid after due date
            $table->unsignedInteger('overdue_count')->default(0);   // currently unpaid, past due

            $table->decimal('total_billed', 14, 2)->default(0);
            $table->decimal('total_paid', 14, 2)->default(0);
            $table->decimal('outstanding', 14, 2)->default(0);

            $table->decimal('on_time_rate', 5, 2)->nullable();      // 0–100 (% of paid invoices on time)
            $table->decimal('avg_days_late', 6, 2)->nullable();     // avg (paid - due), paid invoices, >=0 clamp

            $table->timestamp('first_activity_at')->nullable();     // earliest invoice/tenancy
            $table->timestamp('last_activity_at')->nullable();      // most recent activity
            $table->timestamp('computed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_credit_profiles');
    }
};
