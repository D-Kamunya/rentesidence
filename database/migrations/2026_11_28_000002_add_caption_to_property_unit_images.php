<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An OPTIONAL short free-text caption on a unit photo (e.g. "Granite counters", "North-facing").
 * Nullable + short; purely additive context tenants can read on the storefront.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_unit_images', function (Blueprint $table) {
            if (! Schema::hasColumn('property_unit_images', 'caption')) {
                $table->string('caption', 140)->nullable()->after('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('property_unit_images', function (Blueprint $table) {
            if (Schema::hasColumn('property_unit_images', 'caption')) {
                $table->dropColumn('caption');
            }
        });
    }
};
