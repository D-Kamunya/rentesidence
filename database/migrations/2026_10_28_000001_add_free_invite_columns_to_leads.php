<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The "Set up on Free" consent-gated invite overlay.
 *
 * An affiliate can invite a lead to activate a FREE owner account directly from the lead card.
 * We deliberately DON'T mint the account on click — these columns track a time-boxed invitation
 * instead, and the owner account is created only when the OWNER accepts (clicks the link and sets
 * their own password). That keeps consent with the owner and stops the owners table filling with
 * phantom, never-used accounts.
 *
 * Modelled as an overlay on the lead (not a new pipeline status) so the lead status enum/machine
 * is untouched. Invite state is derived from these columns (see FreeOnboardingService::inviteState).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('free_invite_token', 64)->nullable()->unique()->after('converted_at');
            $table->string('free_invite_email')->nullable()->after('free_invite_token');
            $table->timestamp('free_invite_sent_at')->nullable()->after('free_invite_email');
            $table->timestamp('free_invite_expires_at')->nullable()->after('free_invite_sent_at');
            $table->timestamp('free_invite_accepted_at')->nullable()->after('free_invite_expires_at');
            $table->unsignedTinyInteger('free_invite_count')->default(0)->after('free_invite_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'free_invite_token',
                'free_invite_email',
                'free_invite_sent_at',
                'free_invite_expires_at',
                'free_invite_accepted_at',
                'free_invite_count',
            ]);
        });
    }
};
