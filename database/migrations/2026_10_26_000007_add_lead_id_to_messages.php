<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links a contact enquiry to the marketplace lead an admin creates from it (Stream 1: route
 * UNattributed inbound enquiries into the affiliate lead pool). Mirrors the existing `owner_id`
 * link (enquiry → onboarded owner) so a message can't be double-routed and the inbox can show
 * "in marketplace".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'lead_id')) {
                $table->unsignedBigInteger('lead_id')->nullable()->after('owner_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'lead_id')) {
                $table->dropColumn('lead_id');
            }
        });
    }
};
