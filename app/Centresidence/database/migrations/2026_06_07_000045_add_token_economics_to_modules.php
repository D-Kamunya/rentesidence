<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module-level token economics defaults (admin-configurable). Deployment copies
 * these into each PropertyModule's ModuleTokenConfig.
 *
 * Pivot (2026-06-20): per-token commission is Centresidence's income share and
 * defaults to 0 — owners keep their utility revenue and pay only the software +
 * gateway infrastructure costs. A commission is set ONLY where Centresidence
 * shares operational value (e.g. reticulated gas), reflecting real maintenance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->decimal('token_units_per_kes', 12, 4)->nullable()->after('token_unit_label');
            $table->decimal('token_commission_per_unit', 12, 4)->default(0)->after('token_units_per_kes');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['token_units_per_kes', 'token_commission_per_unit']);
        });
    }
};
