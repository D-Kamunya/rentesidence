<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-time reconciliation: earlier provisioning stored the device→unit mapping
 * in `devices.metadata.unit_id`; it is now the authoritative `property_unit_id`
 * column (drives consumption→wallet attribution). Copy any existing mappings
 * across so already-deployed meters resolve correctly.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('devices', 'property_unit_id')) {
            return;
        }

        DB::table('devices')
            ->whereNull('property_unit_id')
            ->whereNotNull('metadata')
            ->orderBy('id')
            ->each(function ($device) {
                $meta = json_decode($device->metadata ?? 'null', true);
                $unitId = is_array($meta) ? ($meta['unit_id'] ?? null) : null;
                if ($unitId) {
                    DB::table('devices')->where('id', $device->id)->update(['property_unit_id' => $unitId]);
                }
            });
    }

    public function down(): void
    {
        // No-op: the source data still lives in metadata.unit_id.
    }
};
