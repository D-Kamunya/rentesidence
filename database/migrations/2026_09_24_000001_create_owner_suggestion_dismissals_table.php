<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Owner Upgrade Advisor computes its suggestions LIVE from the owner's current state
 * (plan, units, rent, infra, SMS) — nothing stale is persisted. The ONLY state we keep is
 * a per-owner, per-suggestion dismissal/snooze, so a dismissed nudge stays hidden across
 * devices (unlike the old localStorage snooze) until it expires. A distinct rail from the
 * affiliate lead-suggestion engine (that stays untouched).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_suggestion_dismissals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id');
            $table->string('suggestion_key', 60);      // e.g. upgrade_transaction, financing, approaching_cap, sms_savings
            $table->timestamp('snoozed_until')->nullable(); // null = dismissed indefinitely
            $table->timestamps();

            $table->unique(['owner_user_id', 'suggestion_key']);
            $table->index('owner_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_suggestion_dismissals');
    }
};
