<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `self_financed_modules` — an owner choosing to fund a module deployment
 * themselves instead of (or after failing to qualify for) partner financing.
 * No finance partner, no facility, no rent deduction: the owner simply pays the
 * hardware + installation cost and the module is deployed. Visible to owner and
 * admin with the same cost transparency as partner financing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('self_financed_modules', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->nullable()->unique();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('catalogue_item_id')->nullable()->constrained('module_pricing_catalogue')->nullOnDelete();

            $table->integer('quantity')->default(1);
            $table->decimal('hardware_cost', 14, 2)->default(0);
            $table->decimal('installation_cost', 14, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);

            $table->enum('status', ['pending_payment', 'paid', 'deploying', 'deployed', 'cancelled'])
                  ->default('pending_payment');

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('deployed_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['owner_id', 'status']);
            $table->index(['property_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('self_financed_modules');
    }
};
