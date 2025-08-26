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
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id')->index();
            $table->unsignedBigInteger('owner_id')->index();
            $table->unsignedBigInteger('subscription_id')->index();
            $table->unsignedBigInteger('subscription_payment_id')->nullable()->index();
            $table->decimal('subscription_amount', 12, 2); // subscription amount
            $table->enum('type', [NEW_CLIENT,RECURRING_CLIENT]);
            $table->integer('period_month')->index();
            $table->integer('period_year')->index();
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
        Schema::dropIfExists('affiliate_commissions');
    }
};
