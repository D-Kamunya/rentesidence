<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks seeded plug-and-play defaults as protected (undeletable), mirroring what
 * ticket_topics already has. Lets the delete guards block removal of the important
 * default document requests / maintenance issues / invoice types (owners can still
 * add + delete their own). Backfills nothing — existing rows default to 0 (deletable);
 * newly seeded defaults set is_default=1.
 */
return new class extends Migration
{
    private array $tables = ['kyc_configs', 'maintenance_issues', 'invoice_types'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'is_default')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->tinyInteger('is_default')->default(0)->after('name');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'is_default')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('is_default');
                });
            }
        }
    }
};
