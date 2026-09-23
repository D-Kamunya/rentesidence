<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `finance_partner_documents` — verification documents uploaded during partner
 * onboarding (handbook §9.2.1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_partner_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('finance_partner_id')->constrained('finance_partners')->cascadeOnDelete();

            $table->enum('document_type', [
                'certificate_of_incorporation',
                'tax_compliance',
                'bank_letter',
                'partnership_agreement',
                'other',
            ]);

            $table->string('file_path');
            $table->boolean('verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index('finance_partner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_partner_documents');
    }
};
