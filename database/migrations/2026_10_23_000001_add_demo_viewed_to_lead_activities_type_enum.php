<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add `demo_viewed` to the lead_activities.type enum — logged when a prospect substantially views
 * the guided demo (the self-serve engagement signal, distinct from the affiliate-marked
 * demo_completed). Without it, MySQL silently stores an empty string for the type.
 */
return new class extends Migration
{
    private const WITH = "ENUM('note_added','temperature_update','demo_scheduled','demo_completed','conversion_requested','conversion_rejected','trial_requested','trial_started','trial_expired','trial_extension','lead_converted','lead_rejected','lead_lost','whatsapp_sent','email_sent','call_made','lead_created','lead_claimed','recycled_to_marketplace','lead_pulled','lead_expired','demo_viewed') NOT NULL";

    private const WITHOUT = "ENUM('note_added','temperature_update','demo_scheduled','demo_completed','conversion_requested','conversion_rejected','trial_requested','trial_started','trial_expired','trial_extension','lead_converted','lead_rejected','lead_lost','whatsapp_sent','email_sent','call_made','lead_created','lead_claimed','recycled_to_marketplace','lead_pulled','lead_expired') NOT NULL";

    public function up(): void
    {
        DB::statement('ALTER TABLE lead_activities MODIFY COLUMN type ' . self::WITH);
    }

    public function down(): void
    {
        DB::statement("DELETE FROM lead_activities WHERE type = 'demo_viewed'");
        DB::statement('ALTER TABLE lead_activities MODIFY COLUMN type ' . self::WITHOUT);
    }
};
