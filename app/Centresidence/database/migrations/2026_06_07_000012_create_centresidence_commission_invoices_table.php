<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `centresidence_commission_invoices` — the primary commission pathway for
 * SUBSCRIPTION-billed owners (handbook §8.2). Per (owner, property, month):
 * base subscription + all metered & non-metered module cost components, with
 * full JSON breakdowns for transparency, plus dual-pathway fallback state.
 *
 * Unique on (owner_id, property_id, billing_month) so the monthly job is
 * idempotent — re-running a cycle updates rather than duplicates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centresidence_commission_invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();

            $table->date('billing_month');

            $table->decimal('subscription_amount', 12, 2)->default(0);

            // [{module_id, module_name, component_name, cost_model, device_count, rate, subtotal}]
            $table->json('metered_commission_breakdown')->nullable();
            $table->decimal('metered_commission_total', 12, 2)->default(0);

            $table->json('non_metered_commission_breakdown')->nullable();
            $table->decimal('non_metered_commission_total', 12, 2)->default(0);

            // Gateway allocation context (not billed here; for transparency).
            $table->json('infrastructure_cost_breakdown')->nullable();

            // subscription + metered_total + non_metered_total.
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->enum('status', ['pending', 'paid', 'overdue', 'partially_paid', 'waived'])
                  ->default('pending');

            // Dual-pathway fallback (token deduction) — driven in WP4.
            $table->boolean('fallback_deduction_active')->default(false);
            $table->timestamp('fallback_deduction_started_at')->nullable();
            $table->timestamp('fallback_metered_cleared_at')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['owner_id', 'property_id', 'billing_month'], 'cs_commission_unique_cycle');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centresidence_commission_invoices');
    }
};
