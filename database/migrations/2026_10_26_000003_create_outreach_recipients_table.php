<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per person in a campaign's paste list. Carries the per-recipient send + follow-up +
 * conversion state so the whole drive is trackable from one screen (no scattered inbox).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('outreach_recipients')) {
            return;
        }
        Schema::create('outreach_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('outreach_campaigns')->cascadeOnDelete();
            $table->string('email')->index();
            $table->string('first_name')->nullable();
            $table->string('status')->default('pending');   // pending | sent | converted | failed
            $table->timestamp('sent_at')->nullable();
            $table->unsignedTinyInteger('follow_up_count')->default(0);
            $table->timestamp('last_follow_up_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'email']);        // no duplicate recipient within a campaign
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_recipients');
    }
};
