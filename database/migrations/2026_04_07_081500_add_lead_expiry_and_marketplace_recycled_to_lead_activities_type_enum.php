<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE lead_activities 
            MODIFY COLUMN type ENUM(
                'note_added',
                'temperature_update',
                'demo_scheduled',
                'demo_completed',
                'conversion_requested',
                'conversion_rejected',
                'trial_requested',
                'trial_started',
                'trial_expired',
                'trial_extention',
                'lead_converted',
                'lead_rejected',
                'lead_lost',
                'whatsapp_sent',
                'email_sent',
                'call_made',
                'lead_created',
                'lead_claimed',
                'recycled_to_marketplace',
                'lead_pulled',
                'lead_expired'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE lead_activities 
            MODIFY COLUMN type ENUM(
                'note_added',
                'temperature_update',
                'demo_scheduled',
                'demo_completed',
                'conversion_requested',
                'conversion_rejected',
                'trial_requested',
                'trial_started',
                'trial_expired',
                'trial_extention',
                'lead_converted',
                'lead_rejected',
                'lead_lost',
                'whatsapp_sent',
                'email_sent',
                'call_made',
                'lead_created',
                'lead_claimed',
                'lead_pulled'
            ) NOT NULL
        ");
    }
};
