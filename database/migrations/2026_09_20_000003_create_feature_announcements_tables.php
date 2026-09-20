<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * In-account "what's new" announcements. Admin publishes a feature announcement (optionally linked
 * to a Knowledge Base article that can embed a YouTube demo); each account sees it once in a modal.
 * Audience-aware + per-user seen-tracking so it's reusable across every role.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('icon')->nullable();       // emoji, e.g. 🎉
            $table->string('link_url')->nullable();    // "Learn more" — a KB article URL, video, etc.
            $table->string('link_label')->nullable();  // defaults to "Learn more"
            $table->string('audience')->default('all'); // 'all' or comma-separated role ids (e.g. "1,5")
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'published_at']);
        });

        Schema::create('feature_announcement_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_announcement_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamp('seen_at')->nullable();
            $table->unique(['feature_announcement_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_announcement_user');
        Schema::dropIfExists('feature_announcements');
    }
};
