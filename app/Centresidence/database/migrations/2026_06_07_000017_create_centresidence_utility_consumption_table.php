<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `utility_consumption` — units drawn down from a utility wallet, typically from
 * device telemetry (handbook Token Engine: usage deduction). Records the
 * balance after each event for an auditable running balance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_consumption', function (Blueprint $table) {
            $table->id();

            $table->foreignId('utility_wallet_id')
                  ->constrained('utility_wallets')
                  ->cascadeOnDelete();

            $table->foreignId('device_id')
                  ->nullable()
                  ->constrained('devices')
                  ->nullOnDelete();

            $table->decimal('units_consumed', 16, 4);
            $table->decimal('balance_after', 16, 4);

            $table->enum('source', ['telemetry', 'manual', 'system'])->default('telemetry');

            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['utility_wallet_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_consumption');
    }
};
