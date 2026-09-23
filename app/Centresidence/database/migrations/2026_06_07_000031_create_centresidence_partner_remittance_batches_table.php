<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `partner_remittance_batches` — aggregated payouts to a partner (handbook
 * §9.5.4). This is the M-Pesa B2C payout seam: a batch groups settled
 * settlement_transactions into a single remittance to the partner.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_remittance_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('finance_partner_id')->constrained('finance_partners')->cascadeOnDelete();

            $table->string('batch_number')->nullable()->unique();
            $table->date('remittance_date')->nullable();
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->integer('facility_count')->default(0);
            $table->integer('transaction_count')->default(0);

            $table->enum('settlement_method', ['bank_transfer', 'mobile_money', 'wallet'])->default('mobile_money');
            $table->string('reference')->nullable();
            $table->enum('status', ['prepared', 'sent', 'confirmed', 'failed', 'retried'])->default('prepared');
            $table->timestamp('confirmation_received_at')->nullable();
            $table->string('reconciliation_file_path')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['finance_partner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_remittance_batches');
    }
};
