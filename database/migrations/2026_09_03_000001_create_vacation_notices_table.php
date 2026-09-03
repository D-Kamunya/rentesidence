<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant "notice to vacate" — Phase 3 of the move-in→move-out lifecycle. A tenant files their
 * intended move-out date; the required notice period (owner policy, default 30 days) is snapshot
 * onto the record so the terms are fixed at filing. Enforced as a DEFAULT, not a hard block: a
 * move-out earlier than the required date is allowed but FLAGGED (meets_notice = false) so the
 * owner can approve / charge rent through the notice-end date. The notice anchors the final rent
 * invoice + deposit settlement (Phase 4).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vacation_notices')) {
            return;
        }

        Schema::create('vacation_notices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('owner_user_id')->index();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->unsignedBigInteger('property_unit_id')->nullable();

            $table->date('notice_date');                     // when the tenant filed
            $table->date('intended_move_out_date');          // when they intend to leave
            $table->unsignedSmallInteger('notice_period_days')->default(30); // snapshot of the policy
            $table->boolean('meets_notice')->default(true);  // intended date honours the required period?

            $table->text('message')->nullable();             // tenant's reason / note

            // pending → filed, awaiting owner; acknowledged → owner has seen/accepted; withdrawn →
            // tenant cancelled; completed → move-out done (Phase 4 settlement closes it).
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_notices');
    }
};
