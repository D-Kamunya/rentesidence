<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `cs_gateways` — LoRaWAN gateway registry. A gateway is a shared piece of
 * Centresidence-owned infrastructure; how its monthly cost is split across the
 * owners/properties it serves is described by infrastructure_topology, NOT by
 * any column here (a gateway may serve multiple owners).
 *
 * NB: prefixed `cs_` to avoid colliding with the legacy PAYMENT `gateways`
 * table (App\Models\Gateway). The handbook calls this `gateways`; the rename is
 * a deliberate deviation for the live database.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_gateways', function (Blueprint $table) {
            $table->id();

            // LoRaWAN gateway EUI / external identifier (e.g. ChirpStack id).
            $table->string('eui')->nullable()->unique();
            $table->string('name');

            $table->string('vendor')->nullable();
            $table->string('model')->nullable();

            // Coarse location for ops dashboards (optional).
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');

            // Simulation harness flag (handbook: build simulation first).
            $table->boolean('is_simulated')->default(false);

            $table->timestamp('last_seen_at')->nullable();
            $table->json('metadata')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_gateways');
    }
};
