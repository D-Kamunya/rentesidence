<?php


use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
                'status_changed',
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
                'temperature_update',
                'note_added',
                'demo_scheduled',
                'status_changed'
            )
        ");
    }
};
