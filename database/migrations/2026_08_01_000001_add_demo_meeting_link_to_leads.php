<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            // Optional video-call link the affiliate can attach when scheduling a
            // demo; when present it's surfaced in the confirmation emails.
            $table->string('demo_meeting_link')->nullable()->after('demo_scheduled_at');
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('demo_meeting_link');
        });
    }
};
