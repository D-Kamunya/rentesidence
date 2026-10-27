<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the "Set up on Free" invite activity types to the lead_activities type enum:
 *   free_invite_sent     — affiliate invited the lead to activate a Free account
 *   free_invite_accepted — the owner accepted and the Free account was created
 *   free_invite_expired  — the invite lapsed unaccepted (logged on resend/cleanup)
 */
return new class extends Migration
{
    private const WITH = "ENUM('note_added','temperature_update','demo_scheduled','demo_completed','conversion_requested','conversion_rejected','trial_requested','trial_started','trial_expired','trial_extension','lead_converted','lead_rejected','lead_lost','whatsapp_sent','email_sent','call_made','lead_created','lead_claimed','recycled_to_marketplace','lead_pulled','lead_expired','demo_viewed','free_invite_sent','free_invite_accepted','free_invite_expired') NOT NULL";

    private const WITHOUT = "ENUM('note_added','temperature_update','demo_scheduled','demo_completed','conversion_requested','conversion_rejected','trial_requested','trial_started','trial_expired','trial_extension','lead_converted','lead_rejected','lead_lost','whatsapp_sent','email_sent','call_made','lead_created','lead_claimed','recycled_to_marketplace','lead_pulled','lead_expired','demo_viewed') NOT NULL";

    public function up(): void
    {
        DB::statement('ALTER TABLE lead_activities MODIFY COLUMN type ' . self::WITH);
    }

    public function down(): void
    {
        DB::statement("DELETE FROM lead_activities WHERE type IN ('free_invite_sent','free_invite_accepted','free_invite_expired')");
        DB::statement('ALTER TABLE lead_activities MODIFY COLUMN type ' . self::WITHOUT);
    }
};
