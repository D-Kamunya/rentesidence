<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Internal e-signature agreements (replaces the DocuSign envelope flow). An owner
 * prepares an agreement (template-autofilled OR an uploaded PDF) for a tenant, who
 * reviews + e-signs it in-portal behind an SMS-OTP challenge. The signed, hashed,
 * certified PDF is stored and downloadable by both parties. Legal integrity comes from
 * document_hash + the immutable agreement_signature_events audit trail.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agreements')) {
            return;
        }

        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id');            // sender
            $table->unsignedBigInteger('tenant_user_id')->nullable(); // signer (a tenant user)
            $table->unsignedBigInteger('agreement_template_id')->nullable(); // the reusable template it came from
            $table->unsignedBigInteger('property_id')->nullable();
            $table->unsignedBigInteger('property_unit_id')->nullable();

            $table->string('title');
            $table->string('source', 16)->default('template');      // 'template' | 'upload' (snapshotted from the template)
            $table->longText('body')->nullable();                   // rendered HTML terms, autofilled + FROZEN at send time
            $table->json('template_data')->nullable();              // snapshot of autofilled fields
            $table->unsignedBigInteger('original_file_id')->nullable(); // uploaded PDF (upload source) → file_managers

            $table->string('status', 16)->default('draft');         // draft|sent|viewed|signed|declined|cancelled

            // Signature capture (bound to the signing ceremony)
            $table->string('signer_full_name')->nullable();
            $table->longText('signature_data')->nullable();         // base64 PNG of the drawn signature
            $table->string('signature_method', 16)->nullable();     // 'drawn'
            $table->timestamp('otp_verified_at')->nullable();
            $table->string('signed_ip', 45)->nullable();
            $table->text('signed_user_agent')->nullable();

            // Artifacts
            $table->string('document_hash', 64)->nullable();        // SHA-256 of the final signed PDF
            $table->unsignedBigInteger('signed_file_id')->nullable(); // certified signed PDF → file_managers

            // Lifecycle
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->string('decline_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_user_id');
            $table->index('tenant_user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
