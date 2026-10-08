<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * System / platform SMS (owner credential delivery, landlord invites, subscription reminders,
 * the critical-alert SMS itself) are sent with owner_user_id = null BY DESIGN — they have no
 * owning owner. But sms_histories.owner_user_id was NOT NULL, so logging any such send threw a
 * 23000 integrity violation: the send succeeded, the history insert failed, the queued job
 * dead-lettered and RESENT on retry (duplicate texts). Making the column nullable lets system
 * SMS log correctly. (SmsHelper::historyStore is also hardened to never throw into the caller, so
 * logging can never break a send regardless of schema.) Guarded + idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sms_histories') && Schema::hasColumn('sms_histories', 'owner_user_id')) {
            Schema::table('sms_histories', function (Blueprint $table) {
                $table->unsignedBigInteger('owner_user_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Intentionally NOT reverting to NOT NULL: a down-migration would fail on any system-SMS
        // rows already logged with a null owner, and NOT NULL was the bug. Leave it nullable.
    }
};
