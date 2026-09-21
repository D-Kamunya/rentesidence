<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * System incidents — a high-signal ops surface for GENUINE platform failures only
 * (failed payouts, broken callback processing, undelivered credentials, dead critical
 * jobs, forged callbacks). NOT a log mirror: rows are written by a handful of targeted
 * hooks, aggregated by dedup_key so a recurring fault is one growing row (not spam),
 * and only critical rows page the admin by SMS — once per throttle window.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);                       // payout_failure, callback_exception, callback_rejected, job_failed, schedule_failed, comms_failure
            $table->string('severity', 12)->default('warning'); // warning | critical
            $table->string('status', 16)->default('open');      // open | acknowledged | resolved
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->string('dedup_key')->nullable();            // repeats with the same key fold onto one open row
            $table->unsignedInteger('occurrences')->default(1);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('alerted_at')->nullable();        // last critical SMS sent (throttle gate)
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'severity']);
            $table->index(['dedup_key', 'status']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_incidents');
    }
};
