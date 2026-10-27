<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds 'suggestion_completed' to the lead_activities type enum.
 *
 * Marking a suggested next-action "Done" (the manual complete, handled off-platform) previously
 * recorded nothing on the lead timeline — only channel actions (WhatsApp/Email/Call) logged an
 * activity. This type lets the manual complete register too, so the timeline reflects every action.
 */
return new class extends Migration
{
    private const WITH = "ENUM('note_added','temperature_update','demo_scheduled','demo_completed','conversion_requested','conversion_rejected','trial_requested','trial_started','trial_expired','trial_extension','lead_converted','lead_rejected','lead_lost','whatsapp_sent','email_sent','call_made','lead_created','lead_claimed','recycled_to_marketplace','lead_pulled','lead_expired','demo_viewed','free_invite_sent','free_invite_accepted','free_invite_expired','suggestion_completed') NOT NULL";

    private const WITHOUT = "ENUM('note_added','temperature_update','demo_scheduled','demo_completed','conversion_requested','conversion_rejected','trial_requested','trial_started','trial_expired','trial_extension','lead_converted','lead_rejected','lead_lost','whatsapp_sent','email_sent','call_made','lead_created','lead_claimed','recycled_to_marketplace','lead_pulled','lead_expired','demo_viewed','free_invite_sent','free_invite_accepted','free_invite_expired') NOT NULL";

    public function up(): void
    {
        DB::statement('ALTER TABLE lead_activities MODIFY COLUMN type ' . self::WITH);
    }

    public function down(): void
    {
        DB::statement("DELETE FROM lead_activities WHERE type = 'suggestion_completed'");
        DB::statement('ALTER TABLE lead_activities MODIFY COLUMN type ' . self::WITHOUT);
    }
};
