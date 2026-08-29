<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Partner opt-in for accelerated repayment. Accelerating lets an owner put a
 * larger share of each rent payment toward the facility, clearing it ahead of
 * term. On reducing-balance products that lowers the total interest the partner
 * earns, so — like early settlement — the partner should decide whether to
 * offer it. Defaults to true to preserve existing behaviour; carried on the
 * partner product and honoured by FinanceFacilityService::setAccelerated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_partner_modules', function (Blueprint $table) {
            $table->boolean('accelerated_repayment_allowed')->default(true)->after('early_repayment_penalty_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('finance_partner_modules', function (Blueprint $table) {
            $table->dropColumn('accelerated_repayment_allowed');
        });
    }
};
