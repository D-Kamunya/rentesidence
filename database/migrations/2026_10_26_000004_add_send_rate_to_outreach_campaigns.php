<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Paced sending: how many emails/hour a campaign trickles out, so a batch of hundreds never bursts
 * past the host's hourly cap (cPanel default ~200/hr; shared hosts 100–500). Default 100 leaves
 * headroom for the system's normal transactional mail. The admin never has to hand-split into waves.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('outreach_campaigns', 'send_per_hour')) {
            return;
        }
        Schema::table('outreach_campaigns', function (Blueprint $table) {
            $table->unsignedSmallInteger('send_per_hour')->default(100)->after('max_follow_ups');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('outreach_campaigns', 'send_per_hour')) {
            Schema::table('outreach_campaigns', function (Blueprint $table) {
                $table->dropColumn('send_per_hour');
            });
        }
    }
};
