<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Financier-facing copy on modules. The owner education explains a module's
 * cashflow benefit; this is the same idea from the FINANCE PARTNER's side — the
 * financing opportunity, demand, and how repayment is secured — so partners can
 * self-onboard instead of being trained manually.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->text('financier_overview')->nullable()->after('cashflow_benefit');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('financier_overview');
        });
    }
};
