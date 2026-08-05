<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `device_commands` — downlink command dispatch log (e.g. credit tokens, close
 * valve, unlock). Generic so any module type can be actuated. The Token Engine
 * (WP4) dispatches a command here when a tenant purchase is processed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();

            $table->foreignId('device_id')
                  ->constrained('devices')
                  ->cascadeOnDelete();

            $table->string('command');
            $table->json('payload')->nullable();

            $table->enum('status', ['queued', 'sent', 'acked', 'failed'])->default('queued');

            // User who triggered it, if any (nullable for system/automatic).
            $table->unsignedBigInteger('issued_by')->nullable();

            $table->timestamp('issued_at')->nullable();
            $table->timestamp('acked_at')->nullable();
            $table->json('response')->nullable();
            $table->string('failure_reason')->nullable();

            $table->timestamps();

            $table->index(['device_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_commands');
    }
};
