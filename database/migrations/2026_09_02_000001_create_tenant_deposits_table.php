<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Register of security deposits ACTUALLY collected and HELD per tenancy — the foundation of the
 * deposit-as-liability model (Phase 2 of the tenant move-in→move-out lifecycle).
 *
 * INVARIANT: a held deposit is the TENANT's money, not the owner's income. It is NEVER commissioned
 * and must never be counted as revenue or financing collateral. This table tracks what is held so
 * "Deposits held" can always be shown DISTINCT from "Rent collected". Rows are created only when a
 * deposit is genuinely paid (not merely invoiced) — the cosmetic units.security_deposit field is a
 * configured amount, NOT a held deposit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_deposits')) {
            return;
        }

        Schema::create('tenant_deposits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id')->index();   // who holds the deposit (Model A)
            $table->unsignedBigInteger('tenant_id')->index();       // whose deposit it is
            $table->unsignedBigInteger('property_id')->nullable();
            $table->unsignedBigInteger('property_unit_id')->nullable();

            // Provenance — the invoice + line that collected it (idempotency key so a deposit is
            // recorded once even if a payment callback fires twice).
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->unsignedBigInteger('invoice_item_id')->nullable()->unique();

            $table->decimal('amount', 12, 2)->default(0);           // amount HELD

            // held → sitting as a liability; refunded → returned to tenant; applied → set against
            // arrears/damages at move-out (both terminal "released" states). Settlement itself is
            // Phase 4 — the transitions live here from the start so the ledger is complete.
            $table->string('status', 20)->default('held')->index();
            $table->decimal('released_amount', 12, 2)->nullable();  // refunded/applied amount at release
            $table->string('release_method', 40)->nullable();       // cash / mpesa / bank / applied-to-arrears
            $table->text('notes')->nullable();

            $table->timestamp('held_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_deposits');
    }
};
