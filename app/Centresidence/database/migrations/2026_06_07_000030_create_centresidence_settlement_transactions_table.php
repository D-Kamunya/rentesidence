<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `settlement_transactions` — individual deduction events from the Deduction
 * Engine (handbook §9.5.3). Each records who a slice of a rent (or token)
 * collection is owed to: a finance partner (repayment) or Centresidence
 * (commission recovery / platform fee).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('settlement_cycle_id')->nullable()->constrained('settlement_cycles')->nullOnDelete();
            $table->foreignId('finance_facility_id')->nullable()->constrained('finance_facilities')->nullOnDelete();

            // Source references (legacy rent order or a token purchase).
            $table->unsignedBigInteger('rent_transaction_id')->nullable();
            $table->unsignedBigInteger('token_purchase_id')->nullable();

            $table->enum('transaction_type', [
                'rent_deduction_principal', 'rent_deduction_interest', 'rent_deduction_penalty',
                'commission_recovery', 'infrastructure_recovery', 'platform_fee', 'token_deduction',
            ]);
            $table->decimal('amount', 12, 2);

            $table->enum('beneficiary_type', ['finance_partner', 'centresidence']);
            $table->unsignedBigInteger('beneficiary_id')->nullable();

            $table->enum('settlement_method', ['bank_transfer', 'mobile_money', 'wallet_credit', 'internal'])->default('mobile_money');
            $table->string('settlement_reference')->nullable();
            $table->timestamp('settled_at')->nullable();

            $table->enum('reconciliation_status', ['pending', 'reconciled', 'disputed', 'failed'])->default('pending');
            $table->text('reconciliation_notes')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['finance_facility_id', 'transaction_type'], 'cs_stxn_fac_type_idx');
            $table->index(['beneficiary_type', 'beneficiary_id'], 'cs_stxn_beneficiary_idx');
            $table->index('rent_transaction_id', 'cs_stxn_rent_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_transactions');
    }
};
