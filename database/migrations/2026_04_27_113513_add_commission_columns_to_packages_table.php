<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->enum('pricing_model', ['free', 'subscription', 'transaction'])
                  ->default('free')
                  ->after('is_trail')
                  ->comment('free=no charge, subscription=paid momthly, transaction=cost deducted from trasactions');

            $table->decimal('commission_markup', 5, 2)
                  ->default(3.00)
                  ->after('pricing_model')
                  ->comment('Added on top of base_commission. Free tier = +3%');

            $table->decimal('commission_discount', 5, 2)
                  ->default(0.00)
                  ->after('commission_markup')
                  ->comment('Subtracted from (base + markup). Paid tiers get discount as reward for upgrading');

            $table->unsignedInteger('max_marketplace_listings')
                  ->default(5)
                  ->after('commission_discount')
                  ->comment('Max active marketplace products. 0 = unlimited');
        });

        // Set free/default package values
        // is_default = 1 or is_trail = 1 are the free tier indicators
        DB::statement("
            UPDATE packages
            SET commission_markup = 3.00,
                commission_discount = 0.00,
                max_marketplace_listings = 5,
                pricing_model = 'free'
            WHERE is_default = 1 OR is_trail = 1
        ");
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'pricing_model',
                'commission_markup',
                'commission_discount',
                'max_marketplace_listings',
            ]);
        });
    }
};