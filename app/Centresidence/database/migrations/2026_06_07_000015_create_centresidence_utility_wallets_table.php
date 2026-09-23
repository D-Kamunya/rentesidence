<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `utility_wallets` — a tenant's prepaid token balance for a metered module on
 * a property (handbook §7 / Token Engine). Balance is in token units (litres,
 * kWh, …). One wallet per (property_module, tenant).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_wallets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_module_id')
                  ->constrained('property_modules')
                  ->cascadeOnDelete();

            // The renter's user account. Nullable for property-level prepay.
            $table->foreignId('tenant_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->string('unit_label')->nullable();

            $table->decimal('balance_units', 16, 4)->default(0);
            $table->decimal('total_purchased_units', 16, 4)->default(0);
            $table->decimal('total_consumed_units', 16, 4)->default(0);

            $table->timestamps();

            $table->unique(['property_module_id', 'tenant_user_id'], 'cs_wallet_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_wallets');
    }
};
