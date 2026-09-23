<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `module_pricing_catalogue` — the standardized unit price of financeable
 * hardware per module (handbook §9/§10). A financing application reads
 * unit_price from here: base_cost = unit_price × quantity, before the platform
 * fee is added on top.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_pricing_catalogue', function (Blueprint $table) {
            $table->id();

            $table->foreignId('module_id')
                  ->constrained('modules')
                  ->cascadeOnDelete();

            $table->string('item_name');
            $table->string('sku')->nullable()->unique();
            $table->text('description')->nullable();

            $table->decimal('unit_price', 12, 2)->default(0);
            $table->char('currency', 3)->default('KES');

            // Unit of sale, e.g. "meter", "lock", "node".
            $table->string('unit_label')->nullable();

            $table->boolean('is_active')->default(true);

            // Optional validity window for price changes.
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();

            $table->timestamps();

            $table->index(['module_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_pricing_catalogue');
    }
};
