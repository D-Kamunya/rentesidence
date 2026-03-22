<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE lead_activities 
            MODIFY type ENUM(
                'note_added',
                'temperature_update',
                'demo_scheduled',
                'demo_completed',
                'conversion_requested',
                'conversion_rejected',
                'trial_requested',
                'trial_started',
                'trial_expired',
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
            MODIFY type ENUM(
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