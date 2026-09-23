<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Merge (B2 stage 4): a subscription order can now bundle the owner's outstanding
 * module-infra into the charge (KES/M-Pesa only). `infra_amount` records the KES
 * infra portion included in `transaction_amount`, so payment-success knows to
 * settle it and there's an audit trail. 0/null for plan-only orders (unchanged).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('subscription_orders', function (Blueprint $table) {
            $table->float('infra_amount')->default(0)->nullable()->after('transaction_amount');
        });
    }

    public function down()
    {
        Schema::table('subscription_orders', function (Blueprint $table) {
            $table->dropColumn('infra_amount');
        });
    }
};
