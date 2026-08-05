<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `settlement_cycles` — a collection/remittance window per facility+partner
 * (handbook §9.5.2). Accumulates what was collected from rent and what was
 * remitted to the partner.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_cycles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('finance_facility_id')->constrained('finance_facilities')->cascadeOnDelete();
            $table->foreignId('finance_partner_id')->constrained('finance_partners')->cascadeOnDelete();

            $table->enum('settlement_type', ['rolling', 'daily', 'weekly', 'monthly'])->default('monthly');
            $table->integer('cycle_number')->default(1);
            $table->date('cycle_start')->nullable();
            $table->date('cycle_end')->nullable();

            $table->decimal('expected_amount', 12, 2)->default(0);
            $table->decimal('collected_amount', 12, 2)->default(0);
            $table->decimal('remitted_amount', 12, 2)->default(0);
            $table->date('remittance_date')->nullable();
            $table->string('remittance_reference')->nullable();

            $table->enum('status', ['pending', 'collecting', 'partial', 'remitted', 'overdue', 'carried_forward'])->default('collecting');
            $table->text('shortfall_reason')->nullable();

            $table->timestamps();

            $table->index(['finance_facility_id', 'cycle_number']);
            $table->index(['finance_partner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_cycles');
    }
};
