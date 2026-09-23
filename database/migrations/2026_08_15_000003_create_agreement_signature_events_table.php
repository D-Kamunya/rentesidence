<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only audit trail for an agreement — every step of the signing ceremony (sent,
 * viewed, OTP sent/verified, consent, signed, declined, downloaded) with who / when /
 * from where. This is the legal evidence that the signature reflects the signer's intent
 * and identity. Never updated or deleted in normal operation.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agreement_signature_events')) {
            return;
        }

        Schema::create('agreement_signature_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agreement_id');
            $table->string('event', 32);                  // sent|viewed|otp_sent|otp_verified|consented|signed|declined|downloaded
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->string('actor_role', 24)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('meta')->nullable();             // masked phone, signature method, doc hash at signing, …
            $table->timestamp('created_at')->nullable();  // append-only; no updated_at

            $table->index('agreement_id');
            $table->index('event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_signature_events');
    }
};
