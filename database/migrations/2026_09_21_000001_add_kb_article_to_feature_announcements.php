<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Let an announcement point at a Knowledge Base ARTICLE (picked from a list) rather than a
 * hand-pasted URL. KB article view routes are per-role, so we store the article id and resolve the
 * correct link for whoever is viewing at render time (FeatureAnnouncement::resolvedLink).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feature_announcements', function (Blueprint $table) {
            $table->unsignedBigInteger('kb_article_id')->nullable()->after('link_label');
        });
    }

    public function down(): void
    {
        Schema::table('feature_announcements', function (Blueprint $table) {
            $table->dropColumn('kb_article_id');
        });
    }
};
