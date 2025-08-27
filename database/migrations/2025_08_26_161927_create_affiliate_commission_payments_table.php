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
    public function up()
    {
        Schema::create('affiliate_commission_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id')->index();
            $table->integer('period_month');
            $table->integer('period_year');
            $table->integer('total_new_clients')->default(0);
            $table->integer('total_recurring_clients')->default(0);
            $table->decimal('new_commissions_amount', 12, 2)->default(0); // sum of subscription amounts of new
            $table->decimal('recurring_commissions_amount', 12, 2)->default(0); // sum amounts of recurring
            $table->decimal('new_commission_payout', 12, 2)->default(0); // new_total * new_rate
            $table->decimal('recurring_commission_payout', 12, 2)->default(0); // recurring_total * tier_rate
            $table->decimal('total_commission_payout', 12, 2)->default(0); // sum of payouts
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_commission_payments');
    }
};
