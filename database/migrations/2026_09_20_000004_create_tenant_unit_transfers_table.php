<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit log of in-place unit transfers — a tenant moved to a different unit in the SAME property
 * WITHOUT closing the tenancy. Records the from/to unit, a snapshot of what was outstanding at the
 * time (the owner made an informed decision; settlement of standing invoices is handled separately /
 * off-system), and an optional note. Preserves continuity + history vs the old close-and-reassign.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_unit_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('property_id');
            $table->unsignedBigInteger('from_unit_id')->nullable();
            $table->unsignedBigInteger('to_unit_id');
            $table->decimal('outstanding_snapshot', 12, 2)->default(0); // unpaid total at transfer
            $table->text('note')->nullable();
            $table->unsignedBigInteger('transferred_by')->nullable(); // owner/user who did it
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_unit_transfers');
    }
};
