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
                'lead_lost'
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
                'trial_started',
                'lead_converted',
                'lead_rejected',
                'lead_lost'
            ) NOT NULL
        ");
    }
};
