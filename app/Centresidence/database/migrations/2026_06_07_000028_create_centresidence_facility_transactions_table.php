<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `facility_transactions` — every financial movement on a facility (handbook
 * §9.4.4). Append-only ledger; the Settlement Engine (WP8) writes repayments
 * here as rent is collected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('finance_facility_id')->constrained('finance_facilities')->cascadeOnDelete();
            $table->foreignId('repayment_schedule_id')->nullable()->constrained('repayment_schedules')->nullOnDelete();

            $table->enum('transaction_type', [
                'disbursement', 'down_payment', 'repayment_principal', 'repayment_interest',
                'repayment_penalty', 'fee', 'adjustment', 'write_off',
            ]);
            $table->decimal('amount', 12, 2);
            $table->enum('direction', ['debit', 'credit']);
            $table->enum('source', ['rent_deduction', 'owner_payment', 'token_deduction', 'manual', 'system'])->default('system');

            $table->unsignedBigInteger('rent_transaction_id')->nullable();
            $table->unsignedBigInteger('settlement_transaction_id')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['finance_facility_id', 'transaction_type'], 'cs_ftxn_fac_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_transactions');
    }
};
