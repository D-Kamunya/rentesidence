<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The reusable SUPPORT rail: any account type (owner, affiliate, finance partner, tenant, and
 * whatever roles come later) can open a threaded support conversation with admin. Deliberately
 * role-agnostic — the requester is just a user + their role, so a new account type plugs in by
 * wiring a sidebar entry, nothing more.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_user_id')->index(); // who opened it (any role)
            $table->unsignedTinyInteger('requester_role')->nullable(); // denormalised for admin filtering/display
            $table->string('subject');
            $table->string('category')->nullable();
            $table->string('status')->default('open');   // open · answered · resolved · closed
            $table->string('priority')->default('normal'); // low · normal · high
            // Two-sided unread flags drive the menu badges without extra queries.
            $table->boolean('admin_unread')->default(true);      // new requester activity awaiting admin
            $table->boolean('requester_unread')->default(false); // new admin activity awaiting the requester
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'admin_unread']);
            $table->index(['requester_user_id', 'requester_unread']);
        });

        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_ticket_id')->index();
            $table->unsignedBigInteger('user_id'); // author (the requester or an admin)
            $table->boolean('is_admin')->default(false);
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_replies');
        Schema::dropIfExists('support_tickets');
    }
};
