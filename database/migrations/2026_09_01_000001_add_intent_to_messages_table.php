<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tag contact messages with an intent (general / trial / partner) so a genuine free-trial
 * or signup enquiry is distinguishable from a random contact — the admin can prioritise it,
 * and the new-message notification flags trial leads loudly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'intent')) {
                $table->string('intent')->nullable()->after('subject');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'intent')) {
                $table->dropColumn('intent');
            }
        });
    }
};
