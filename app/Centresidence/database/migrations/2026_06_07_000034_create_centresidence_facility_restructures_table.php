<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `facility_restructures` — new terms agreed after a default (handbook §9.5.5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_restructures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('finance_facility_id')->constrained('finance_facilities')->cascadeOnDelete();
            $table->foreignId('facility_default_id')->constrained('facility_defaults')->cascadeOnDelete();

            $table->decimal('new_interest_rate', 5, 2)->default(0);
            $table->integer('new_repayment_months')->default(0);
            $table->decimal('new_monthly_target', 12, 2)->default(0);
            $table->decimal('new_deduction_percentage', 5, 2)->default(0);
            $table->date('new_maturity_date')->nullable();
            $table->decimal('restructure_fee', 12, 2)->default(0);

            $table->boolean('approved_by_partner')->default(false);
            $table->boolean('approved_by_owner')->default(false);
            $table->date('effective_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index('finance_facility_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_restructures');
    }
};
