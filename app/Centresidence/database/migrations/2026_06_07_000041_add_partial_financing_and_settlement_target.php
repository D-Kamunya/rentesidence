<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two related money refinements (decided with user 2026-06-08):
 *
 *  1. PARTIAL FINANCING — owners with cash can put a down-payment toward the
 *     deployment and finance only the remainder. `owner_contribution` is what
 *     the owner pays up front; `financed_amount` (= requested_amount − owner
 *     contribution) is what the partner advances and the owner repays with
 *     interest. Defaults of 0 / requested keep the full-finance path identical.
 *
 *  2. SETTLEMENT TARGET — for infrastructure modules Centresidence is the
 *     official INSTALLER, so the facility (and any down-payment) is payable to
 *     Centresidence, not the owner. `modules.settlement_target` captures the
 *     payee so future owner-payable module types can differ. Defaults to
 *     'centresidence' for everything that exists today.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_applications', function (Blueprint $table) {
            $table->decimal('owner_contribution', 14, 2)->default(0)->after('requested_amount');
            $table->decimal('financed_amount', 14, 2)->default(0)->after('owner_contribution');
        });

        Schema::table('finance_facilities', function (Blueprint $table) {
            $table->decimal('owner_contribution', 14, 2)->default(0)->after('disbursed_amount');
        });

        Schema::table('modules', function (Blueprint $table) {
            // 'centresidence' = facility paid to the installer; 'owner' = paid to owner.
            $table->string('settlement_target', 20)->default('centresidence')->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('finance_applications', function (Blueprint $table) {
            $table->dropColumn(['owner_contribution', 'financed_amount']);
        });
        Schema::table('finance_facilities', function (Blueprint $table) {
            $table->dropColumn('owner_contribution');
        });
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('settlement_target');
        });
    }
};
