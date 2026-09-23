<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `facility_defaults` — created when a facility breaches its default threshold
 * (handbook §9.5.5). Snapshots the outstanding balances at default and tracks
 * the collections/recovery process.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_defaults', function (Blueprint $table) {
            $table->id();

            $table->foreignId('finance_facility_id')->constrained('finance_facilities')->cascadeOnDelete();

            $table->timestamp('defaulted_at')->nullable();
            $table->integer('days_past_due_at_default')->default(0);

            $table->decimal('outstanding_principal_at_default', 14, 2)->default(0);
            $table->decimal('outstanding_interest_at_default', 14, 2)->default(0);
            $table->decimal('outstanding_penalty_at_default', 14, 2)->default(0);
            $table->decimal('total_outstanding_at_default', 14, 2)->default(0);

            $table->enum('default_reason', ['payment_failure', 'property_vacant', 'owner_dispute', 'owner_unreachable', 'other'])
                  ->default('payment_failure');
            $table->enum('collections_status', ['internal_collections', 'external_collections', 'legal_proceedings', 'negotiating', 'resolved'])
                  ->default('internal_collections');
            $table->unsignedBigInteger('collections_assignee')->nullable();

            $table->date('last_contact_date')->nullable();
            $table->date('next_action_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('resolved_at')->nullable();
            $table->enum('resolution_type', ['restructured', 'recovered_full', 'recovered_partial', 'written_off'])->nullable();
            $table->decimal('recovery_amount', 14, 2)->default(0);
            $table->decimal('write_off_amount', 14, 2)->default(0);

            $table->timestamps();

            $table->index(['finance_facility_id', 'collections_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_defaults');
    }
};
