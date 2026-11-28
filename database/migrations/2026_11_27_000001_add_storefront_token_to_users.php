<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A stable, opaque per-owner token for the public Vacancy Storefront share link
 * (/vacancies/o/{token}). Lazily generated the first time an owner opens their share
 * hub (User::ensureStorefrontToken), so the URL is permanent — post the QR once on the
 * premises forever and it always renders the owner's live vacancy state.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'storefront_token')) {
                $table->string('storefront_token', 64)->nullable()->unique()->after('remember_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'storefront_token')) {
                $table->dropColumn('storefront_token');
            }
        });
    }
};
