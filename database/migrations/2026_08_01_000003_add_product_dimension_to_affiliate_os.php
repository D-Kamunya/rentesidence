<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Affiliate OS WP-A — the product/vertical dimension (see docs/affiliate-os-design.md).
 * One affiliate works many products; every lead and commission belongs to exactly
 * one. Additive + backfilled: existing rows default to 'property_sales' (the original
 * Centresidence motion, = config('affiliate_os.default_product')), so nothing changes
 * behaviourally until spokes are added.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('product')->default('property_sales')->index()->after('affiliate_id');
        });

        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->string('product')->default('property_sales')->index()->after('affiliate_id');
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('product');
        });

        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->dropColumn('product');
        });
    }
};
