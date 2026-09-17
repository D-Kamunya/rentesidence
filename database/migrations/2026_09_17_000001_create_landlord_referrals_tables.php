<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invite-a-landlord funnel — the referral primitive + the attribution ledger.
 *
 *  - landlord_referral_codes : each tenant's single, stable, shareable invite code
 *    (the /invite/{code} link). One row per referring tenant.
 *  - landlord_referrals      : the attribution ledger — one row per invited landlord,
 *    carrying the reward state machine (pending → lead_created → confirmed → paid /
 *    clawed_back / rejected / expired) and the anti-abuse trail.
 *
 * The ledger is always built; the cash reward is a config-gated RULE on top (see
 * config/referrals.php), so the funnel works whether cash is on or off.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landlord_referral_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');           // the referring tenant (users.id)
            $table->string('code', 32)->unique();            // stable, shareable
            $table->timestamps();

            $table->unique('user_id');                        // one code per tenant
        });

        Schema::create('landlord_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_user_id');   // the tenant who invited (users.id)
            $table->string('code', 32);                       // the referrer's code at invite time (trace)

            // Who was invited (as the tenant knows them / as the landlord filled in).
            $table->string('invitee_name')->nullable();
            $table->string('invitee_phone')->nullable();
            $table->string('invitee_email')->nullable();
            $table->string('invitee_company')->nullable();

            // Reward state machine.
            // pending      : invite recorded, no lead yet
            // lead_created : the invited landlord submitted the intake form → a Lead exists
            // confirmed    : the landlord became a real customer → reward accrued, holding
            // paid         : reward paid out after the holding period
            // clawed_back  : owner churned / refunded inside the window → reversed
            // rejected     : admin / anti-abuse rejected it
            // expired      : the lead expired without ever converting
            $table->string('status', 20)->default('pending');

            // Links into the existing owner lead pipeline (never a self-register path).
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();   // owners.id once converted

            // The reward itself (resolved from config at confirm time, snapshotted here).
            $table->string('reward_type', 12)->nullable();        // cash | credit | none
            $table->decimal('reward_amount', 12, 2)->default(0);
            $table->string('currency', 8)->nullable();
            $table->string('trigger_reason', 30)->nullable();     // first_subscription | revenue_threshold | manual

            // Lifecycle timestamps.
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('held_until')->nullable();          // payable on/after this
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('clawed_back_at')->nullable();

            // Anti-abuse: whether a human must clear it before payout, and a free-form trail
            // (shared phone/device/M-Pesa signals, velocity flags, review notes).
            $table->boolean('needs_review')->default(false);
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index('referrer_user_id');
            $table->index('status');
            $table->index('lead_id');
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landlord_referrals');
        Schema::dropIfExists('landlord_referral_codes');
    }
};
