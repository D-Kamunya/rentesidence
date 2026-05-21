<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('affiliate_commission_payments', function (Blueprint $table) {
            $table->decimal('rent_commissions_amount', 12, 2)->default(0)
                  ->after('recurring_commission_payout');
            $table->decimal('rent_commission_payout', 12, 2)->default(0)
                  ->after('rent_commissions_amount');
            $table->decimal('marketplace_commissions_amount', 12, 2)->default(0)
                  ->after('rent_commission_payout');
            $table->decimal('marketplace_commission_payout', 12, 2)->default(0)
                  ->after('marketplace_commissions_amount');
        });
    }
    
    public function down(): void
    {
        Schema::table('affiliate_commission_payments', function (Blueprint $table) {
            $table->dropColumn([
                'rent_commissions_amount',
                'rent_commission_payout',
                'marketplace_commissions_amount',
                'marketplace_commission_payout',
            ]);
        });
    }
};
