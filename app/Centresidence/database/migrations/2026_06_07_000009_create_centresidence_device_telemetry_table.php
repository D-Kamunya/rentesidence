<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `device_telemetry` — generic time-series readings from devices (consumption,
 * battery, signal, …). Kept metric-agnostic so any module type can report
 * without schema changes. High-volume; indexed for per-device time queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_telemetry', function (Blueprint $table) {
            $table->id();

            $table->foreignId('device_id')
                  ->constrained('devices')
                  ->cascadeOnDelete();

            // e.g. consumption, battery_level, rssi.
            $table->string('metric');
            $table->decimal('value', 16, 4)->nullable();
            $table->string('unit')->nullable();

            // Original decoded payload for audit/debug.
            $table->json('raw')->nullable();

            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['device_id', 'metric', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_telemetry');
    }
};
