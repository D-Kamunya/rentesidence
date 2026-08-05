<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional capacity on a gateway. A LoRaWAN gateway can serve many devices, but
 * an operator may want to cap how many are bound to one gateway. Null = no
 * limit (default), preserving existing behaviour.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cs_gateways', function (Blueprint $table) {
            $table->unsignedInteger('max_devices')->nullable()->after('model');
        });
    }

    public function down(): void
    {
        Schema::table('cs_gateways', function (Blueprint $table) {
            $table->dropColumn('max_devices');
        });
    }
};
