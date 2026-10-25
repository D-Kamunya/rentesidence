<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Closes the affiliate attribution loop at the inbound finish line. The shareable referral link
 * (route('frontend', ['referral' => $code])) lands a prospect on the site; when they submit the
 * contact form, we now record WHICH affiliate referred them so that when an admin onboards the owner
 * from that message, the owner is credited to the affiliate (owners.affiliate_id). Before this, the
 * viral link leaked — an affiliate-driven inbound signup earned the affiliate nothing.
 *
 * referral_affiliate_id = affiliates.id (the Affiliate ROW id — matches owners.affiliate_id's FK),
 * NOT the user id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'referral_code')) {
                $table->string('referral_code')->nullable()->after('intent');
            }
            if (! Schema::hasColumn('messages', 'referral_affiliate_id')) {
                $table->unsignedBigInteger('referral_affiliate_id')->nullable()->after('referral_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            foreach (['referral_code', 'referral_affiliate_id'] as $col) {
                if (Schema::hasColumn('messages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
