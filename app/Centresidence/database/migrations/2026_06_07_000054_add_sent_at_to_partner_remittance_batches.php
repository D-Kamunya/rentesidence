<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a bank remittance is marked sent, stamp the moment — so the batch carries
 * a full timeline: prepared (remittance_date) → sent (sent_at) → confirmed
 * (confirmation_received_at). Backfills existing sent/confirmed batches from
 * updated_at so they don't read blank.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_remittance_batches', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable()->after('status');
        });

        // Backfill: any batch already past 'prepared' was sent at some point.
        \Illuminate\Support\Facades\DB::table('partner_remittance_batches')
            ->whereIn('status', ['sent', 'confirmed'])
            ->whereNull('sent_at')
            ->update(['sent_at' => \Illuminate\Support\Facades\DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('partner_remittance_batches', function (Blueprint $table) {
            $table->dropColumn('sent_at');
        });
    }
};
