<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module CATEGORY — the financing network isn't infra-only. Every financeable module now
 * carries a category (infra / developmental / lifestyle / financial), all plugging into the
 * same rent-secured at-source servicing (the hub). Existing modules default to 'infra'.
 * The category label/order/blurb live in config('centresidence.module_categories').
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('modules') && ! Schema::hasColumn('modules', 'category')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->string('category')->default('infra')->after('key')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('modules') && Schema::hasColumn('modules', 'category')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
};
