<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `finance_partner_modules` — the heart of the marketplace (handbook §9.2.2):
 * each partner defines a financing product per module, with rates, fees, tenor
 * range, settlement preferences, and underwriting requirements.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_partner_modules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('finance_partner_id')->constrained('finance_partners')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();

            $table->string('product_name');
            $table->string('product_code')->nullable();

            $table->enum('interest_rate_type', ['fixed', 'reducing_balance', 'flat'])->default('reducing_balance');
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->enum('interest_calculation_method', ['monthly_rest', 'daily_rest', 'flat_upfront'])->default('monthly_rest');
            $table->decimal('penalty_rate', 5, 2)->default(0);

            $table->decimal('processing_fee_percentage', 5, 2)->default(0);
            $table->decimal('processing_fee_flat', 12, 2)->default(0);

            $table->decimal('min_amount', 12, 2)->default(0);
            $table->decimal('max_amount', 12, 2)->default(0);
            $table->integer('min_repayment_months')->default(1);
            $table->integer('max_repayment_months')->default(12);
            $table->enum('repayment_frequency', ['daily', 'weekly', 'biweekly', 'monthly'])->default('monthly');

            // Underwriting requirements.
            $table->integer('required_cashflow_months')->default(0);
            $table->decimal('min_occupancy_rate', 5, 2)->default(0);
            $table->decimal('max_rent_deduction_percentage', 5, 2)->default(0);
            $table->boolean('requires_existing_obligation_check')->default(false);
            $table->decimal('max_total_obligation_ratio', 5, 2)->nullable();
            $table->boolean('requires_owner_kyc')->default(false);
            $table->boolean('requires_property_valuation')->default(false);

            // Settlement preferences.
            $table->boolean('daily_settlement_enabled')->default(false);
            $table->boolean('monthly_settlement_enabled')->default(true);
            $table->integer('settlement_day')->nullable();

            $table->enum('disbursement_method', ['escrow_to_deployment', 'direct_to_owner', 'milestone_based'])->default('escrow_to_deployment');

            $table->integer('grace_period_days')->default(0);
            $table->integer('default_threshold_days')->default(0);
            $table->boolean('early_repayment_allowed')->default(true);
            $table->decimal('early_repayment_penalty_percentage', 5, 2)->nullable();
            $table->boolean('insurance_required')->default(false);
            $table->string('insurance_provider')->nullable();

            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->integer('display_priority')->default(0);
            $table->text('terms_and_conditions_text')->nullable();
            $table->json('configuration_json')->nullable();

            $table->timestamps();

            $table->index(['module_id', 'status', 'display_priority']);
            $table->index('finance_partner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_partner_modules');
    }
};
