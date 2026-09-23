<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `token_purchases` — a tenant token purchase with full embedded-commission
 * accounting (handbook §7.3). For each purchase we snapshot the token config
 * and record: units credited, Centresidence commission (embedded, NOT a
 * tenant-visible line item), gross owner revenue, any fallback intercepted from
 * owner revenue toward overdue metered commission, and net owner revenue.
 *
 * Token purchases are EXEMPT from the 1% transaction fee.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('token_purchases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('utility_wallet_id')
                  ->constrained('utility_wallets')
                  ->cascadeOnDelete();

            $table->foreignId('property_module_id')
                  ->constrained('property_modules')
                  ->cascadeOnDelete();

            $table->foreignId('tenant_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Gross KES paid by the tenant.
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('KES');

            // Units credited (tenant always receives these in full).
            $table->decimal('units', 16, 4);
            $table->string('unit_label')->nullable();

            // Config snapshot for audit.
            $table->decimal('units_per_kes_snapshot', 12, 4);
            $table->decimal('commission_per_unit_snapshot', 12, 4);

            // Accounting split.
            $table->decimal('centresidence_commission', 12, 2)->default(0);
            $table->decimal('owner_revenue_gross', 12, 2)->default(0);
            $table->decimal('fallback_deducted', 12, 2)->default(0);
            $table->decimal('owner_revenue_net', 12, 2)->default(0);

            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->string('payment_reference')->nullable();

            // Downlink command that credited the device, if dispatched.
            $table->foreignId('device_command_id')
                  ->nullable()
                  ->constrained('device_commands')
                  ->nullOnDelete();

            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();

            $table->index(['property_module_id', 'status']);
            $table->index('tenant_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('token_purchases');
    }
};
