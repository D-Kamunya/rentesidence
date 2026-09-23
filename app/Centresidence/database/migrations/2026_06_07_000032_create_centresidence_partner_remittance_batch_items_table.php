<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `partner_remittance_batch_items` — links settlement transactions to a
 * remittance batch (handbook §9.5.4).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_remittance_batch_items', function (Blueprint $table) {
            $table->id();

            // Explicit FK names — auto names would exceed MySQL's 64-char limit.
            $table->unsignedBigInteger('partner_remittance_batch_id');
            $table->foreign('partner_remittance_batch_id', 'cs_prbi_batch_fk')
                  ->references('id')->on('partner_remittance_batches')->cascadeOnDelete();

            $table->unsignedBigInteger('settlement_transaction_id');
            $table->foreign('settlement_transaction_id', 'cs_prbi_stxn_fk')
                  ->references('id')->on('settlement_transactions')->cascadeOnDelete();

            $table->foreignId('facility_id')->nullable()->constrained('finance_facilities')->nullOnDelete();

            $table->decimal('amount', 12, 2)->default(0);

            $table->timestamp('created_at')->nullable();

            $table->index('partner_remittance_batch_id', 'cs_prbi_batch_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_remittance_batch_items');
    }
};
