<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `finance_applications` — an owner's application to finance module hardware
 * (handbook §9.3.2). Carries the auto-calculated facility maths (base cost,
 * platform fee, requested amount), the lifecycle status, and audit snapshots.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_applications', function (Blueprint $table) {
            $table->id();

            $table->string('application_number')->nullable()->unique();

            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('finance_partner_id')->constrained('finance_partners')->cascadeOnDelete();
            $table->foreignId('finance_partner_module_id')->constrained('finance_partner_modules')->cascadeOnDelete();
            $table->foreignId('catalogue_item_id')->nullable()->constrained('module_pricing_catalogue')->nullOnDelete();

            $table->integer('quantity')->default(1);

            // Auto-calculated facility maths (handbook §9.3 / §2 select & calculate).
            $table->decimal('base_cost', 14, 2)->default(0);
            $table->decimal('platform_fee_percentage', 5, 2)->default(0);
            $table->decimal('platform_fee_amount', 12, 2)->default(0);
            $table->decimal('requested_amount', 14, 2)->default(0);
            $table->decimal('approved_amount', 14, 2)->nullable();

            $table->decimal('interest_rate_snapshot', 5, 2)->nullable();
            $table->decimal('repayment_percentage', 5, 2)->nullable();
            $table->integer('repayment_months')->nullable();
            $table->decimal('estimated_monthly_repayment', 12, 2)->nullable();

            $table->enum('status', [
                'draft', 'submitted', 'under_review', 'approved',
                'rejected', 'disbursed', 'withdrawn', 'cancelled',
            ])->default('draft');

            $table->text('rejection_reason')->nullable();
            $table->json('underwriting_result_json')->nullable();

            $table->boolean('owner_consent')->default(false);
            $table->timestamp('owner_consent_at')->nullable();
            $table->json('application_data_json')->nullable();

            // State-transition timestamps.
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('under_review_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();

            $table->timestamps();

            $table->index(['owner_id', 'status']);
            $table->index(['finance_partner_id', 'status']);
            $table->index('property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_applications');
    }
};
