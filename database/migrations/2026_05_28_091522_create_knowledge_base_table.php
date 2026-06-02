<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Categories ──────────────────────────────────────────────────────
        Schema::create('kb_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable()->comment('Heroicon or SVG name');
            $table->text('description')->nullable();
            // Which audience sees this category
            // values: 'owners', 'affiliates', 'both'
            $table->enum('audience', ['owners', 'affiliates', 'both'])->default('both');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Articles ─────────────────────────────────────────────────────────
        Schema::create('kb_articles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kb_category_id')
                  ->nullable()
                  ->constrained('kb_categories')
                  ->nullOnDelete();

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();

            // article | video | document | link
            $table->enum('type', ['article', 'video', 'document', 'link'])->default('article');

            // Who can see this article
            // values: 'owners', 'affiliates', 'both'
            $table->enum('audience', ['owners', 'affiliates', 'both'])->default('both');

            // published | draft | archived
            $table->enum('status', ['published', 'draft', 'archived'])->default('draft');

            // Rich HTML body — used when type = 'article'
            $table->longText('body')->nullable();

            // Plain text excerpt shown on cards
            $table->text('excerpt')->nullable();

            // For type = 'video': YouTube / Vimeo embed URL
            $table->string('video_url')->nullable();

            // For type = 'link': external URL
            $table->string('external_url')->nullable();

            // For type = 'document': stored file path (storage/app/kb_documents/...)
            $table->string('document_path')->nullable();
            $table->string('document_original_name')->nullable();
            $table->string('document_mime_type')->nullable();
            $table->unsignedBigInteger('document_size')->nullable()->comment('bytes');

            // Ordering within a category
            $table->unsignedInteger('sort_order')->default(0);

            // Simple view counter
            $table->unsignedBigInteger('views_owner')->default(0);
            $table->unsignedBigInteger('views_affiliate')->default(0);

            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'audience']);
            $table->index('kb_category_id');
            $table->index('type');
        });

        // ── Article views (per-user read tracking) ──────────────────────────
        Schema::create('kb_article_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kb_article_id')->constrained('kb_articles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // 'owner' | 'affiliate'
            $table->string('viewer_type', 20);
            $table->timestamp('last_viewed_at');
            $table->unsignedInteger('view_count')->default(1);
            $table->timestamps();

            $table->unique(['kb_article_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_article_views');
        Schema::dropIfExists('kb_articles');
        Schema::dropIfExists('kb_categories');
    }
};