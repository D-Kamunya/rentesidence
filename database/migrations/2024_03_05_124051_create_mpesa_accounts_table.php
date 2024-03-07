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
        Schema::create('mpesa_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_type');
            $table->unsignedBigInteger('gateway_id');
            $table->unsignedBigInteger('owner_user_id');
            $table->string('paybill')->nullable();
            $table->string('till_number')->nullable();
            $table->string('status')->default(0);
            $table->string('account_name')->nullable();
            $table->string('passkey');
            $table->timestamps();

                // Foreign keys
            $table->foreign('gateway_id')->references('id')->on('gateways')->onDelete('cascade');
            $table->foreign('owner_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpesa_accounts');
    }
};
