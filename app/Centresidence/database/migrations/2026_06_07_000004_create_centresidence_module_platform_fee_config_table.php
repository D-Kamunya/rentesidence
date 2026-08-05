<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `module_platform_fee_config` — admin-set platform fee percentage charged on
 * financing applications, per module (handbook §9.3). The finance partner
 * underwrites the TOTAL (base_cost + platform_fee); the fee is retained by
 * Centresidence on disbursement.
 *
 * Kept as its own time-bounded config (rather than a column on modules) so fee
 * changes are auditable and a rate can be snapshotted onto an application.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_platform_fee_config', function (Blueprint $table) {
            $table->id();

            $table->foreignId('module_id')
                  ->constrained('modules')
                  ->cascadeOnDelete();

            // e.g. 10.00 = 10% of base_cost.
            $table->decimal('fee_percentage', 5, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();

            $table->timestamps();

            $table->index(['module_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_platform_fee_config');
    }
};
