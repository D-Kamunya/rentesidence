<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `application_documents` — documents uploaded for a specific application
 * (handbook §9.3.3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('finance_application_id')->constrained('finance_applications')->cascadeOnDelete();

            $table->enum('document_type', [
                'owner_id', 'property_title', 'rent_roll', 'bank_statement',
                'utility_bill', 'property_photo', 'other',
            ]);

            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();

            $table->boolean('verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('rejection_reason')->nullable();

            $table->timestamps();

            $table->index('finance_application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_documents');
    }
};
