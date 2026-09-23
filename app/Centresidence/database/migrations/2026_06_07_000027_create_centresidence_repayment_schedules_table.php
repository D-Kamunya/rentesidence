<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `repayment_schedules` — generated at facility creation, one row per period
 * (handbook §9.4.3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repayment_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('finance_facility_id')->constrained('finance_facilities')->cascadeOnDelete();

            $table->integer('period_number');
            $table->date('due_date');

            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->decimal('principal_due', 12, 2)->default(0);
            $table->decimal('interest_due', 12, 2)->default(0);
            $table->decimal('total_due', 12, 2)->default(0);

            $table->decimal('principal_paid', 12, 2)->default(0);
            $table->decimal('interest_paid', 12, 2)->default(0);
            $table->decimal('penalty_paid', 12, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);

            $table->decimal('closing_balance', 14, 2)->default(0);

            $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'waived'])->default('pending');
            $table->integer('days_overdue')->default(0);

            $table->timestamps();

            $table->index(['finance_facility_id', 'period_number']);
            $table->index(['finance_facility_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repayment_schedules');
    }
};
