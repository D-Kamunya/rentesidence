<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deliverability safety net: addresses we will NOT send to. Populated automatically by the
 * EmailGuard heuristic (obvious test/reserved addresses) and by the admin (known hard-bouncers),
 * and consulted on every outbound email via the MessageSending hook. Protects sender reputation
 * from being eroded by automated mail to dead/test addresses — the human-in-the-loop lives on
 * System Health, where each suppressed send surfaces as an incident.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_suppressions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('reason')->nullable();          // why it's suppressed
            $table->string('source', 20)->default('auto');  // auto | admin | bounce
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_suppressions');
    }
};
