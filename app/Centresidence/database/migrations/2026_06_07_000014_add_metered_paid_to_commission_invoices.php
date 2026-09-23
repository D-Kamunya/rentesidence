<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks how much of a commission invoice's metered (fallback-eligible) portion
 * has been recovered via token deduction (handbook §8.1 fallback / §6.3).
 * Separate from the non-metered portion, which is NEVER token-recovered.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centresidence_commission_invoices', function (Blueprint $table) {
            $table->decimal('metered_paid_total', 12, 2)->default(0)->after('metered_commission_total');
        });
    }

    public function down(): void
    {
        Schema::table('centresidence_commission_invoices', function (Blueprint $table) {
            $table->dropColumn('metered_paid_total');
        });
    }
};
