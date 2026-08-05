<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `application_status_history` — immutable log of every application status
 * change (handbook §9.3.3). Append-only audit trail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_status_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('finance_application_id')->constrained('finance_applications')->cascadeOnDelete();

            $table->string('from_status')->nullable();
            $table->string('to_status');

            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('change_reason')->nullable();
            $table->json('metadata_json')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index('finance_application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_history');
    }
};
