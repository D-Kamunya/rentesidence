<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `accelerated_repayment` — owner opt-in. When false (default) the Deduction
 * Engine collects only up to the monthly target per cycle, then pauses. When
 * true, every rent payment keeps deducting until the facility clears, letting
 * the owner settle early (and, on reducing-balance facilities, save interest).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_facilities', function (Blueprint $table) {
            $table->boolean('accelerated_repayment')->default(false)->after('deduction_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('finance_facilities', function (Blueprint $table) {
            $table->dropColumn('accelerated_repayment');
        });
    }
};
