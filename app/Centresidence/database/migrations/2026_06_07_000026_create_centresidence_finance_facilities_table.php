<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `finance_facilities` — an active financing facility created when an
 * application is approved (handbook §9.4.2). Tracks balances, repayment target,
 * the rent-deduction percentage, and lifecycle status.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_facilities', function (Blueprint $table) {
            $table->id();

            $table->string('facility_number')->nullable()->unique();
            $table->foreignId('finance_application_id')->constrained('finance_applications')->cascadeOnDelete();
            $table->foreignId('finance_partner_id')->constrained('finance_partners')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();

            $table->decimal('disbursed_amount', 14, 2)->default(0);
            $table->decimal('principal_amount', 14, 2)->default(0);
            $table->decimal('platform_fee_amount', 12, 2)->default(0);
            $table->boolean('platform_fee_settled')->default(false);
            $table->timestamp('platform_fee_settled_at')->nullable();

            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->enum('interest_calculation_method', ['monthly_rest', 'daily_rest', 'flat_upfront'])->default('monthly_rest');
            $table->decimal('penalty_rate', 5, 2)->default(0);
            $table->decimal('processing_fee_charged', 12, 2)->default(0);

            $table->decimal('total_repayable', 14, 2)->default(0);
            $table->decimal('outstanding_principal', 14, 2)->default(0);
            $table->decimal('outstanding_interest', 14, 2)->default(0);
            $table->decimal('outstanding_penalty', 14, 2)->default(0);

            $table->decimal('monthly_target', 12, 2)->default(0);
            $table->decimal('deduction_percentage', 5, 2)->default(0);
            $table->integer('repayment_months')->default(0);

            $table->date('disbursement_date')->nullable();
            $table->date('first_repayment_date')->nullable();
            $table->date('maturity_date')->nullable();

            $table->integer('grace_period_days')->default(0);
            $table->integer('default_threshold_days')->default(0);
            $table->integer('days_past_due')->default(0);

            $table->enum('status', [
                'active', 'completed', 'suspended', 'defaulted',
                'restructured', 'recovered', 'written_off',
            ])->default('active');

            $table->text('suspension_reason')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('resumed_at')->nullable();
            $table->timestamp('defaulted_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index(['owner_id', 'status']);
            $table->index(['finance_partner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_facilities');
    }
};
