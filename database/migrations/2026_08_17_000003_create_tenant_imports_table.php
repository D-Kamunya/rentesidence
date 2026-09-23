<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ledger for bulk tenant/unit imports. One row per uploaded file, carrying it through the
 * two-phase flow (upload → validated preview → queued processing → done) and recording
 * counts + a row-level error report so a company onboarding thousands of records can see
 * exactly what imported and what needs fixing.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_imports')) {
            return;
        }

        Schema::create('tenant_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id')->index();
            $table->string('original_filename');
            $table->string('stored_path');                 // uploaded CSV on the storage disk
            // uploaded | previewed | processing | completed | completed_with_errors | failed
            $table->string('status', 30)->default('uploaded');
            $table->json('options')->nullable();           // invite channel, notify, etc.

            // Preview (dry-run) counts.
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);

            // Processing (commit) counts.
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);

            $table->json('error_report')->nullable();      // [{row, errors[]}] (capped)
            $table->json('summary')->nullable();           // preview summary for the UI
            $table->text('failure_reason')->nullable();    // whole-import failure (parse, etc.)

            $table->timestamp('previewed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_imports');
    }
};
