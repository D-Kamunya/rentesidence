<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * In-system outreach campaigns — the fix for V1's manual hot-mess (hand-typing hundreds of
 * addresses, pasting copy, then forgetting to follow up). An admin pastes a recipient list, the
 * system sends a CS-layout email to each (recruitment campaigns auto-inject the live invite link),
 * tracks who converted by email-match, and chases non-responders automatically on a bounded schedule.
 * Deliberately separate from the system's event-driven transactional mail.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('outreach_campaigns')) {
            return;
        }
        Schema::create('outreach_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('recruitment');  // recruitment | general
            $table->string('subject');
            $table->text('body');                              // may contain {{first_name}} / {{invite_link}}
            $table->string('status')->default('draft');        // draft | sending | sent
            $table->unsignedSmallInteger('follow_up_days')->default(4);   // wait before the first nudge
            $table->unsignedTinyInteger('max_follow_ups')->default(1);    // bounded — never spam
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_campaigns');
    }
};
