<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the legacy DocuSign envelope table — the internal e-signature system (agreements
 * / agreement_templates / agreement_signature_events) fully replaces it, and there is no
 * live data to preserve (operations were paused before onboarding clients).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('agreement_histories');
    }

    public function down(): void
    {
        // Best-effort recreate of the old shape (envelope-centric) if ever rolled back.
        if (Schema::hasTable('agreement_histories')) {
            return;
        }

        Schema::create('agreement_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('bulk_envelope_status')->nullable();
            $table->string('envelope_id')->nullable();
            $table->string('error_details')->nullable();
            $table->string('recipient_signing_uri')->nullable();
            $table->string('recipient_signing_uri_error')->nullable();
            $table->string('status')->nullable();
            $table->string('status_date_time')->nullable();
            $table->string('uri')->nullable();
            $table->tinyInteger('is_test')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
