<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invite-a-landlord reward payouts.
 *
 * A payout batches a tenant's confirmed, held-period-elapsed rewards and pays them to their
 * registered M-Pesa (reusing MpesaB2CService + the shared B2CResult/B2CTimeout callback, exactly
 * like affiliate/owner withdrawals). The referrals it covers are linked by payout_id, so a failed
 * payout releases them back to payable and a successful one marks them paid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_user_id');  // the tenant being paid (users.id)
            $table->decimal('amount', 12, 2);
            $table->string('currency', 8)->nullable();
            $table->string('phone', 32)->nullable();          // paid to the tenant's registered number

            // pending → processing (B2C in-flight) → paid / failed / cancelled
            $table->string('status', 12)->default('pending');
            $table->string('settlement_method', 12)->nullable(); // b2c | manual
            $table->string('mpesa_reference')->nullable();        // ConversationID — correlates the callback
            $table->string('transaction_id')->nullable();         // M-Pesa receipt on success
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('referrer_user_id');
            $table->index('status');
            $table->index('mpesa_reference');
        });

        Schema::table('landlord_referrals', function (Blueprint $table) {
            // Links a confirmed reward to the payout that is (or was) settling it. Setting it
            // reserves the referral against double-payment; a failed payout clears it again.
            $table->unsignedBigInteger('payout_id')->nullable()->after('owner_id');
            $table->index('payout_id');
        });
    }

    public function down(): void
    {
        Schema::table('landlord_referrals', function (Blueprint $table) {
            $table->dropIndex(['payout_id']);
            $table->dropColumn('payout_id');
        });
        Schema::dropIfExists('referral_payouts');
    }
};
