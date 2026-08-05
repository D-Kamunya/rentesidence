<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Partial-payment tracking on the owner infrastructure invoice, so it can be
 * recovered from rent over several payments (like the commission invoice's
 * metered_paid_total). `paid_total` accumulates what's been recovered;
 * outstanding = total_amount − paid_total.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owner_infrastructure_invoices', function (Blueprint $table) {
            $table->decimal('paid_total', 12, 2)->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('owner_infrastructure_invoices', function (Blueprint $table) {
            $table->dropColumn('paid_total');
        });
    }
};
