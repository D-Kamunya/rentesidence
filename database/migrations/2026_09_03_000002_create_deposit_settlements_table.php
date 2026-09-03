<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Move-out deposit SETTLEMENT (Phase 4). The itemized record of how a held deposit was resolved:
 * held − documented deductions (arrears/damages/charges) = refund due. Model A — the owner refunds
 * OUTSIDE our rails and records it here; this is the mutual, itemized record (attestation status
 * added for the tenant confirm/dispute in the next slice). No money is moved by us (that's Phase 5).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('deposit_settlements')) {
            Schema::create('deposit_settlements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->index();
                $table->unsignedBigInteger('owner_user_id')->index();
                $table->unsignedBigInteger('property_id')->nullable();
                $table->unsignedBigInteger('property_unit_id')->nullable();
                $table->unsignedBigInteger('vacation_notice_id')->nullable();

                $table->decimal('deposit_held', 12, 2)->default(0);     // snapshot of what was held
                $table->decimal('total_deductions', 12, 2)->default(0); // sum of the item lines
                $table->decimal('refund_amount', 12, 2)->default(0);    // held − deductions (≥ 0)

                $table->string('refund_method', 40)->nullable();        // cash / mpesa / bank
                $table->string('refund_reference', 100)->nullable();    // tx code / note
                $table->date('refund_date')->nullable();

                // recorded → owner logged it; confirmed/disputed → tenant attestation (next slice).
                $table->string('status', 20)->default('recorded')->index();
                $table->text('notes')->nullable();
                $table->timestamp('settled_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('deposit_settlement_items')) {
            Schema::create('deposit_settlement_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('deposit_settlement_id')->index();
                $table->string('type', 20)->default('other');   // arrears / damage / charge / other
                $table->string('description', 255);
                $table->decimal('amount', 12, 2)->default(0);
                $table->unsignedBigInteger('invoice_id')->nullable(); // when a line settles an arrears invoice
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_settlement_items');
        Schema::dropIfExists('deposit_settlements');
    }
};
