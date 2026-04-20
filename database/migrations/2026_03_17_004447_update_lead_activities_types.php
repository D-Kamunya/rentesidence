<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
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
                'lead_rejected',
                'lead_lost'
            )
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE lead_activities 
            MODIFY type ENUM(
                'note_added',
                'temperature_update',
                'demo_scheduled',
                'status_changed'
            )
        ");
    }
};
