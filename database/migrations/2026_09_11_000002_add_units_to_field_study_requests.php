<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How many units the owner wants the custom install (e.g. reticulated gas) to cover — captured on
 * the survey request (capped to the property's actual unit count), so the installer quotes against
 * a concrete scope. Mirrors the "apply to all N units" pattern in the normal finance flow.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('field_study_requests') && ! Schema::hasColumn('field_study_requests', 'units')) {
            Schema::table('field_study_requests', fn (Blueprint $t) => $t->unsignedInteger('units')->nullable()->after('property_id'));
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('field_study_requests') && Schema::hasColumn('field_study_requests', 'units')) {
            Schema::table('field_study_requests', fn (Blueprint $t) => $t->dropColumn('units'));
        }
    }
};
