<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `application_document_requirements` — which documents a partner requires for
 * a given product (handbook §9.3.3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_document_requirements', function (Blueprint $table) {
            $table->id();

            // Explicit FK name — auto name would exceed MySQL's 64-char limit.
            $table->unsignedBigInteger('finance_partner_module_id');
            $table->foreign('finance_partner_module_id', 'cs_adr_fpm_fk')
                  ->references('id')->on('finance_partner_modules')->cascadeOnDelete();

            $table->enum('document_type', [
                'owner_id', 'property_title', 'rent_roll', 'bank_statement',
                'utility_bill', 'property_photo', 'other',
            ]);

            $table->boolean('is_required')->default(true);
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index('finance_partner_module_id', 'cs_adr_fpm_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_document_requirements');
    }
};
