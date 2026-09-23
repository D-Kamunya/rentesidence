<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a per-unit installation cost to the pricing catalogue, so the full cost
 * of deploying a module (hardware + installation) is explicit — visible to
 * owners and admins whether the module is partner-financed or self-financed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_pricing_catalogue', function (Blueprint $table) {
            $table->decimal('installation_cost', 12, 2)->default(0)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('module_pricing_catalogue', function (Blueprint $table) {
            $table->dropColumn('installation_cost');
        });
    }
};
