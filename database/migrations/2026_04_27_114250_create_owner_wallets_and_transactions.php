<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()
                  ->comment('Owner user_id — one wallet per owner');
            $table->decimal('balance', 12, 2)->default(0.00)
                  ->comment('Current withdrawable balance in system currency');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_wallet_id');
            $table->unsignedBigInteger('product_order_id')->nullable()
                  ->comment('Nullable for future non-marketplace transactions');
            $table->decimal('gross_amount', 12, 2)
                  ->comment('Full amount paid by tenant');
            $table->decimal('commission_rate', 5, 2)
                  ->comment('Effective commission % applied at time of transaction');
            $table->decimal('commission_amount', 12, 2)
                  ->comment('Amount deducted by Centresidence');
            $table->decimal('net_amount', 12, 2)
                  ->comment('Amount credited to owner wallet after commission');
            $table->enum('type', ['credit', 'debit', 'withdrawal', 'refund'])
                  ->default('credit');
            $table->string('description')->nullable();
            $table->foreign('owner_wallet_id')->references('id')->on('owner_wallets')->onDelete('cascade');
            $table->foreign('product_order_id')->references('id')->on('product_orders')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('owner_wallets');
    }
};