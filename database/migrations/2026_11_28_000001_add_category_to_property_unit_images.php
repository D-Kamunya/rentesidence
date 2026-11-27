<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An OPTIONAL room label on a unit photo (living_room, kitchen, bathroom…), so an owner can walk
 * the unit tapping a room and snapping — and tenants can see which room each photo is. Nullable on
 * purpose: tagging is never required, so the frictionless "just snap" flow still works.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_unit_images', function (Blueprint $table) {
            if (! Schema::hasColumn('property_unit_images', 'category')) {
                $table->string('category', 30)->nullable()->after('file_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('property_unit_images', function (Blueprint $table) {
            if (Schema::hasColumn('property_unit_images', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};
