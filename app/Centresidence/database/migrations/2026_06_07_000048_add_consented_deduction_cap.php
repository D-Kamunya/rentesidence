<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-owner consented deduction cap. The 60% global rent-deduction ceiling
 * protects owners by default, but at facility apply-time an owner may opt into
 * a higher PERSONAL cap (to keep the agreed repayment term). Captured on the
 * application, carried to the facility, and read by the DeductionEngine.
 * Null = use the global default.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_applications', function (Blueprint $table) {
            $table->unsignedTinyInteger('consented_deduction_cap')->nullable()->after('repayment_percentage');
        });
        Schema::table('finance_facilities', function (Blueprint $table) {
            $table->unsignedTinyInteger('consented_deduction_cap')->nullable()->after('deduction_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('finance_applications', function (Blueprint $table) {
            $table->dropColumn('consented_deduction_cap');
        });
        Schema::table('finance_facilities', function (Blueprint $table) {
            $table->dropColumn('consented_deduction_cap');
        });
    }
};
