<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A device (meter/lock) is deployed under a property_module, but a metered
 * uplink must debit ONE tenant's utility wallet. When a module spans several
 * units (e.g. 4 water meters), the device→unit link is how an uplink resolves
 * the right wallet. Nullable + self-healing: single-meter modules resolve by
 * their sole wallet and don't need it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('devices') && ! Schema::hasColumn('devices', 'property_unit_id')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->unsignedBigInteger('property_unit_id')->nullable()->after('property_module_id');
                $table->index('property_unit_id', 'cs_devices_unit_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('devices', 'property_unit_id')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropIndex('cs_devices_unit_idx');
                $table->dropColumn('property_unit_id');
            });
        }
    }
};
