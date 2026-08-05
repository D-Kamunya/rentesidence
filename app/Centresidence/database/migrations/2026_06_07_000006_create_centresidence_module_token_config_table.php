<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `module_token_config` — token economics for a metered property_module
 * (handbook §7.2). When a tenant buys tokens, the price reflects the owner's
 * configured rate, with Centresidence's commission embedded (not a separate
 * tenant-visible line item).
 *
 * NOTE — corrected formula (handbook §7.2 typo). The handbook prints
 *   owner_revenue_per_token_unit = units_per_kes − commission_per_token_unit
 * which mixes units-per-KES with KES-per-unit. The §7.3 worked example is the
 * source of truth, giving:
 *   price_per_unit         = 1 / units_per_kes            (KES per unit)
 *   owner_revenue_per_unit = price_per_unit − centresidence_commission_per_unit
 * This is enforced in ModuleTokenConfig::computeOwnerRevenuePerUnit().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_token_config', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_module_id')
                  ->constrained('property_modules')
                  ->cascadeOnDelete();

            // e.g. Litres, KG, kWh.
            $table->string('token_unit_label');

            // Units a tenant receives per KES 1 (e.g. 5.0 = 5 litres / KES).
            $table->decimal('units_per_kes', 12, 4);

            // Sum of is_fallback_eligible cost components expressed per token
            // unit (KES per unit). Admin-set / derived from cost components.
            $table->decimal('centresidence_commission_per_token_unit', 12, 4)->default(0);

            // KES the owner nets per token unit. Stored for transparency and
            // auditability; kept consistent via the corrected formula above.
            $table->decimal('owner_revenue_per_token_unit', 12, 4)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique('property_module_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_token_config');
    }
};
