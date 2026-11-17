<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SMS deliverability safety net — the sibling of email_suppressions. Numbers here are NEVER sent to.
 * Sending to invalid / test / seed numbers gets the batch rejected by the gateway, eroding our sender
 * reputation and delivery rate. Populated by the SmsGuard heuristic (obvious test ranges) and by the
 * admin, and consulted on every SMS send. Numbers are stored NORMALISED (e.g. 254700000101).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_suppressions', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->unique();        // normalised MSISDN, e.g. 254700000101
            $table->string('reason')->nullable();           // why it's suppressed
            $table->string('source', 20)->default('auto');  // auto | admin | bounce
            $table->timestamps();
        });

        // Seed the known demo/seed tenant numbers (0700000101 / 0700000102) so they're explicit and
        // visible in the admin list — not just caught by the heuristic range.
        foreach (['254700000101', '254700000102'] as $n) {
            DB::table('sms_suppressions')->insert([
                'number'     => $n,
                'reason'     => 'Demo/seed test number',
                'source'     => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_suppressions');
    }
};
