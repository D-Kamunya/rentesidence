<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id');
            $table->enum('type', ['purchase', 'deduct', 'refund', 'manual_topup']);
            $table->unsignedInteger('quantity');
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->unsignedInteger('balance_before');
            $table->unsignedInteger('balance_after');
            $table->string('reference')->nullable();
            $table->string('description')->nullable();
            $table->enum('status', ['success', 'failed', 'pending'])->default('success');
            $table->timestamps();

            $table->index('owner_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_credit_transactions');
    }
};