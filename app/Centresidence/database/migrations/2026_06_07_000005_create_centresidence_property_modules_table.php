<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `property_modules` — an instance of a module type activated on a property
 * (optionally a specific unit). This is what the billing engines iterate over:
 * `active_meter_count` is the live count of active devices for the module and
 * drives per_active_device cost components and commission calculation.
 *
 * FKs point at the legacy tables (properties, property_units, users) by
 * reference only — the module never alters those tables. `owner_id` mirrors
 * Property.owner_id (→ users.id) so billing can be grouped by owner directly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_modules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_id')
                  ->constrained('properties')
                  ->cascadeOnDelete();

            // Nullable: a module may be property-wide or scoped to one unit.
            $table->foreignId('property_unit_id')
                  ->nullable()
                  ->constrained('property_units')
                  ->nullOnDelete();

            $table->foreignId('module_id')
                  ->constrained('modules')
                  ->cascadeOnDelete();

            // Billing entity — denormalised from the property's owner (users.id).
            $table->foreignId('owner_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Live count of active devices for this module on this property.
            // Maintained by Device events (DeviceActivated / DeviceDeactivated).
            $table->unsignedInteger('active_meter_count')->default(0);

            // How the OWNER is billed for this module's costs (handbook §6).
            $table->enum('billing_model', ['subscription', 'transaction'])
                  ->default('subscription');

            $table->enum('status', ['active', 'inactive', 'suspended'])
                  ->default('active');

            $table->json('config')->nullable();

            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();

            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index(['owner_id', 'status']);
            $table->index('module_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_modules');
    }
};
