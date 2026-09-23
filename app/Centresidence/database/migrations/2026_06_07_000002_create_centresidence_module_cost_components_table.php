<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `module_cost_components` — replaces the old single flat commission_rate with
 * a composable model (handbook §5). Every chargeable module has one or more
 * components (e.g. platform_software_fee + lorawan_gateway_usage) that sum to
 * what Centresidence earns, each with independent billing rules.
 *
 * `is_fallback_eligible` is the tenant-protection switch: only metered
 * components may be recovered from token revenue if an invoice goes overdue.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_cost_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('module_id')
                  ->constrained('modules')
                  ->cascadeOnDelete();

            // e.g. platform_software_fee, lorawan_gateway_usage
            $table->string('component_name');

            // How the rate is applied.
            $table->enum('cost_model', [
                'per_active_device',
                'per_gateway_allocation',
                'per_unit_consumed',
                'flat_monthly',
            ]);

            // Stored at rate precision (sub-cent allowed for per_unit_consumed).
            $table->decimal('rate', 12, 4)->default(0);
            $table->char('currency', 3)->default('KES');

            // Device active 15/30 days pays ~50% when true.
            $table->boolean('is_prorated')->default(true);

            // Only charged when the device is linked to an active gateway via
            // infrastructure_topology.
            $table->boolean('requires_gateway')->default(false);

            // Recoverable via token deduction. Must be false for non-metered.
            $table->boolean('is_fallback_eligible')->default(false);

            $table->unsignedInteger('display_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index(['module_id', 'status']);
            $table->index('is_fallback_eligible');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_cost_components');
    }
};
