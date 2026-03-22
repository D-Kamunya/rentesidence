<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE leads 
            MODIFY status ENUM(
                'active',
                'demo_scheduled',
                'demo_completed',
                'pending_conversion',
                'converted',
                'lost',
                'rejected',
                'expired'
            ) DEFAULT 'active'
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE leads 
            MODIFY status ENUM(
                'active',
                'demo_scheduled',
                'demo_completed',
                'converted',
                'lost',
                'rejected',
                'expired'
            ) DEFAULT 'active'
        ");
    }
};
