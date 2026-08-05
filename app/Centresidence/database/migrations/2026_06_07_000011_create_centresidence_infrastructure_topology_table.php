<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `infrastructure_topology` — THE billing source of truth for Centresidence-
 * owned infrastructure (handbook §4.2). It answers: "which infrastructure
 * asset serves which paying entity, and how is the cost allocated?"
 *
 * Polymorphic to the asset (gateway today; network_server / mesh_node later)
 * so the cost engine is asset-agnostic. One row = one owner's share of one
 * asset for one property. Multi-owner buildings get multiple rows summing to
 * 100%; multi-gateway properties get multiple rows each at their own share.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infrastructure_topology', function (Blueprint $table) {
            $table->id();

            // Polymorphic asset reference.
            $table->enum('infrastructure_type', ['gateway', 'network_server', 'mesh_node'])
                  ->default('gateway');
            $table->unsignedBigInteger('infrastructure_id');

            // Billed entity + physical property served.
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();

            // This owner's share of the asset's cost.
            $table->decimal('allocation_percentage', 5, 2)->default(100.00);

            $table->enum('billing_model', [
                'flat_monthly',
                'per_active_device_capped',
                'per_active_device_uncapped',
            ])->default('per_active_device_uncapped');

            // Centresidence's internal cost / target revenue for this allocation.
            $table->decimal('monthly_base_cost', 12, 2)->default(0);

            // Ceiling per device if billing_model is capped.
            $table->decimal('cost_per_device_max', 12, 2)->nullable();

            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');

            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->timestamps();

            // Explicit short names — MySQL caps identifiers at 64 chars and the
            // auto-generated name for this composite would exceed it.
            $table->index(['infrastructure_type', 'infrastructure_id'], 'cs_topo_asset_idx');
            $table->index(['owner_id', 'status'], 'cs_topo_owner_idx');
            $table->index(['property_id', 'status'], 'cs_topo_property_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infrastructure_topology');
    }
};
