<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `devices` — generic infrastructure endpoints. Per the handbook, a device is
 * NOT a utility-specific object: what it *is* (water meter, lock) comes from
 * the property_module it belongs to. Devices communicate via a gateway, which
 * is how infrastructure cost is attributed (devices.gateway_id → gateways.id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();

            $table->string('dev_eui')->nullable()->unique();
            $table->string('name')->nullable();

            // The activated module instance this device belongs to. Drives
            // active_meter_count per property_module.
            $table->foreignId('property_module_id')
                  ->nullable()
                  ->constrained('property_modules')
                  ->nullOnDelete();

            // The gateway this device communicates through (for cost attribution).
            $table->foreignId('gateway_id')
                  ->nullable()
                  ->constrained('cs_gateways')
                  ->nullOnDelete();

            $table->enum('status', ['provisioning', 'active', 'inactive', 'decommissioned'])
                  ->default('provisioning');

            $table->boolean('is_simulated')->default(false);

            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();

            $table->json('metadata')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['property_module_id', 'status']);
            $table->index(['gateway_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
