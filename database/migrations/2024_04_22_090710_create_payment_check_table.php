<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCheckTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_check', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_payment_id')->nullable();
            $table->unsignedBigInteger('invoice_payment_id')->nullable();
            $table->foreign('subscription_payment_id')->references('id')->on('subscription_orders')->onDelete('cascade');
            $table->foreign('invoice_payment_id')->references('id')->on('orders')->onDelete('cascade');
            $table->unsignedInteger('check_count')->default(0);
            $table->timestamp('last_check_at')->nullable();
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
        Schema::dropIfExists('payment_check');
    }
}
