<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retire the two per-bucket credit ledgers now that everything reads/writes the unified
 * owner_credit_transactions table (their rows were copied over in the create migration).
 * Runs after 2026_08_17_000001, so the data is already migrated by the time we drop.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sms_credit_transactions');
        Schema::dropIfExists('agreement_credit_transactions');
    }

    public function down(): void
    {
        // Recreate the legacy shapes (empty) so a rollback leaves a working schema. Data is
        // not restored — the unified table remains the source of truth.
        if (! Schema::hasTable('sms_credit_transactions')) {
            Schema::create('sms_credit_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('owner_user_id');
                $table->string('type', 24);
                $table->unsignedInteger('quantity');
                $table->decimal('amount_paid', 10, 2)->nullable();
                $table->unsignedInteger('balance_before');
                $table->unsignedInteger('balance_after');
                $table->string('reference')->nullable();
                $table->string('payment_id')->nullable();
                $table->string('description')->nullable();
                $table->string('status', 12)->default('success');
                $table->timestamps();
                $table->index('owner_user_id');
            });
        }

        if (! Schema::hasTable('agreement_credit_transactions')) {
            Schema::create('agreement_credit_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('owner_user_id');
                $table->string('type', 24);
                $table->unsignedInteger('quantity');
                $table->decimal('amount_paid', 10, 2)->nullable();
                $table->unsignedInteger('balance_before');
                $table->unsignedInteger('balance_after');
                $table->string('reference')->nullable();
                $table->string('payment_id')->nullable();
                $table->string('description')->nullable();
                $table->string('status', 12)->default('success');
                $table->timestamps();
                $table->index('owner_user_id');
            });
        }
    }
};
