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
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            // Make sure columns are nullable before setting onDelete('set null')
            $table->unsignedBigInteger('affiliate_id')->nullable()->change();
            $table->unsignedBigInteger('owner_id')->nullable()->change();
            $table->unsignedBigInteger('subscription_id')->nullable()->change();
            $table->unsignedBigInteger('subscription_payment_id')->nullable()->change();

            $table->foreign('affiliate_id')
                  ->references('id')
                  ->on('affiliates')
                  ->onDelete('cascade');

            $table->foreign('owner_id')
                  ->references('id')
                  ->on('owners')
                  ->onDelete('set null');

            $table->foreign('subscription_id')
                  ->references('id')
                  ->on('owner_packages')
                  ->onDelete('set null');

            $table->foreign('subscription_payment_id')
                  ->references('id')
                  ->on('subscription_orders')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->dropForeign(['affiliate_id']);
            $table->dropForeign(['owner_id']);
            $table->dropForeign(['subscription_id']);
            $table->dropForeign(['subscription_payment_id']);
        });
    }
};
