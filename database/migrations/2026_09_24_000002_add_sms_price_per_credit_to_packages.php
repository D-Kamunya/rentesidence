<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-domain SMS pricing: a plan may set its own KES price per SMS credit, so paid domains
 * can be cheaper than Free as an upgrade pull. NULL = fall back to the global sms_credit_price.
 * Resolved (and floored above our gateway cost) in CreditService::pricePerUnit for the sms bucket.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('packages', 'sms_price_per_credit')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->decimal('sms_price_per_credit', 8, 2)->nullable()->after('monthly_sms_credits');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('packages', 'sms_price_per_credit')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->dropColumn('sms_price_per_credit');
            });
        }
    }
};
