<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks when an owner last sent a TEMPLATED reminder SMS for an invoice, so the
 * owner-triggered reminder can enforce a per-invoice cooldown (anti-spam — our
 * shortcode reaches the tenant, so we cap how often it can fire).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('last_reminder_sms_at')->nullable()->after('payment_token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('last_reminder_sms_at');
        });
    }
};
