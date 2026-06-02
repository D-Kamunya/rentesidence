<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Blog Categories ─────────────────────────────────────────────────
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->nullable()->default('#185FA5');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Blog Posts ──────────────────────────────────────────────────────
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('blog_category_id')
                  ->nullable()
                  ->constrained('blog_categories')
                  ->nullOnDelete();
            
            $table->foreignId('author_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->string('title');
            $table->string('slug')->unique();
            
            // published | draft | scheduled
            $table->enum('status', ['published', 'draft', 'scheduled'])->default('draft');
            
            // Rich HTML body
            $table->longText('body');
            
            // Plain text excerpt
            $table->text('excerpt')->nullable();
            
            // Featured image
            $table->string('featured_image')->nullable();
            $table->string('featured_image_alt')->nullable();
            
            // SEO fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            // Engagement metrics
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('shares_count')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);
            
            // Reading time in minutes
            $table->unsignedInteger('reading_time')->nullable();
            
            // Featured post
            $table->boolean('is_featured')->default(false);
            
            // Tags (comma-separated or JSON)
            $table->json('tags')->nullable();
            
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['status', 'published_at']);
            $table->index('blog_category_id');
            $table->index('is_featured');
        });

        // ── Blog Comments ───────────────────────────────────────────────────
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('blog_post_id')
                  ->constrained('blog_posts')
                  ->cascadeOnDelete();
            
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('blog_comments')
                  ->nullOnDelete();
            
            $table->string('author_name');
            $table->string('author_email');
            $table->text('content');
            
            // approved | pending | spam
            $table->enum('status', ['approved', 'pending', 'spam'])->default('pending');
            
            $table->unsignedInteger('likes_count')->default(0);
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('blog_post_id');
            $table->index('status');
        });

        // ── Blog Post Likes ─────────────────────────────────────────────────
        Schema::create('blog_post_likes', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('blog_post_id')
                  ->constrained('blog_posts')
                  ->cascadeOnDelete();
            
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->timestamps();
            
            $table->unique(['blog_post_id', 'user_id']);
        });

        // ── Blog Post Shares ────────────────────────────────────────────────
        Schema::create('blog_post_shares', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('blog_post_id')
                  ->constrained('blog_posts')
                  ->cascadeOnDelete();
            
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            // facebook | twitter | linkedin | whatsapp | copy_link | email
            $table->string('platform', 50);
            
            $table->timestamps();
            
            $table->index('blog_post_id');
        });

        // ── Blog Post Views ─────────────────────────────────────────────────
        Schema::create('blog_post_views', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('blog_post_id')
                  ->constrained('blog_posts')
                  ->cascadeOnDelete();
            
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('session_id')->nullable();
            
            $table->timestamp('viewed_at');
            
            $table->index('blog_post_id');
        });

        // ── Blog Subscribers ───────────────────────────────────────────────
        Schema::create('blog_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_views');
        Schema::dropIfExists('blog_post_shares');
        Schema::dropIfExists('blog_post_likes');
        Schema::dropIfExists('blog_comments');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('blog_subscribers');
    }
};