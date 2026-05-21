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
        Schema::table('subscription_orders', function (Blueprint $table) {
            $table->enum('pricing_model', ['free', 'subscription', 'transaction'])
                    ->default('free')
                    ->after('package_type')
                    ->comment('free=no charge, subscription=paid momthly, transaction=cost deducted from trasactions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_orders', function (Blueprint $table) {
            $table->dropColumn('pricing_model');
        });
    }
};
