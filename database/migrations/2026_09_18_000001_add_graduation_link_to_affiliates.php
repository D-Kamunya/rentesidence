<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links an affiliate account back to the TENANT it graduated from (the invite-a-landlord
 * upgrade path). This is the anchor for the session-swap switch — from the tenant we find their
 * affiliate account, and from the affiliate we find the origin tenant — and for admin conversion
 * tracking (Graduations).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->unsignedBigInteger('origin_tenant_user_id')->nullable()->after('user_id');
            $table->timestamp('graduated_at')->nullable()->after('origin_tenant_user_id');
            $table->index('origin_tenant_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropIndex(['origin_tenant_user_id']);
            $table->dropColumn(['origin_tenant_user_id', 'graduated_at']);
        });
    }
};
