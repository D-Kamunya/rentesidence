<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `modules` — the catalogue of module *types* the platform can deploy
 * (Water Meter, Gas Meter, Smart Lock, Parking, …). This is the backbone of
 * the "don't hardcode modules" principle: a module is a configurable row, and
 * its pricing/commission/token behaviour lives in the satellite tables.
 *
 * The single most important attribute is `is_metered`: metered modules have a
 * token/consumption flow (and so their costs can be `is_fallback_eligible`),
 * non-metered modules (locks, parking) never recover cost from token revenue.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();

            // Stable machine key (e.g. water_meter) + human label.
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // Metered (token/consumption) vs non-metered (presence-only).
            $table->boolean('is_metered')->default(true);

            // Whether instances of this module normally communicate via a
            // LoRaWAN gateway (drives requires_gateway cost components).
            $table->boolean('requires_gateway')->default(true);

            // Default token unit label for metered modules (Litres, KG, kWh).
            $table->string('token_unit_label')->nullable();

            // Whether this module type can be financed via the marketplace.
            $table->boolean('is_financeable')->default(false);

            // Extensible, rule-driven behaviour without schema changes.
            $table->json('config')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);

            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_active', 'is_metered']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
