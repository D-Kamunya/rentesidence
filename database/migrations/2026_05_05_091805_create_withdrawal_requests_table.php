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
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_wallet_id')->constrained('owner_wallets')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('phone', 20);               // +2547XXXXXXXX
            $table->string('status', 20)->default('pending'); // pending|approved|rejected
            $table->string('mpesa_reference')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
