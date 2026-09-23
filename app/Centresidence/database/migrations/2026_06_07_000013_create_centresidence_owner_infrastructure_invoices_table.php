<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `owner_infrastructure_invoices` — the separate infrastructure invoice for
 * NON-METERED costs that cannot be recovered from token revenue (handbook
 * §6.2 / §8.3). Used for TRANSACTION-billed owners: metered costs are token-
 * deducted, but non-metered costs (locks, parking) get a direct invoice here.
 *
 * Unique on (owner_id, property_id, billing_month) for idempotency.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_infrastructure_invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();

            $table->date('billing_month');

            // [{module_id, module_name, component_name, device_count, rate, subtotal}]
            $table->json('breakdown_json')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->enum('status', ['pending', 'paid', 'overdue', 'partially_paid', 'waived'])
                  ->default('pending');

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['owner_id', 'property_id', 'billing_month'], 'cs_infra_unique_cycle');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_infrastructure_invoices');
    }
};
