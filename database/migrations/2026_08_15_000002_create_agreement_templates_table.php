<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An owner's REUSABLE agreement template — set up once (a plug-and-play default is
 * seeded, or the owner customises / uploads their own), then reused for every tenant
 * until they choose to change it. Each sent agreement snapshots the template content at
 * send time, so editing a template never alters already-signed agreements.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agreement_templates')) {
            return;
        }

        Schema::create('agreement_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id');
            $table->string('name');
            $table->string('source', 16)->default('template');   // 'template' (HTML) | 'upload' (PDF)
            $table->longText('body')->nullable();                // editable HTML terms with {{placeholders}}
            $table->unsignedBigInteger('original_file_id')->nullable(); // uploaded PDF → file_managers
            $table->boolean('is_default')->default(false);       // the seeded plug-and-play default (undeletable)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_templates');
    }
};
