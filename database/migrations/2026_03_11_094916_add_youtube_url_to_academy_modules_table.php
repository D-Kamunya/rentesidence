<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academy_modules', function (Blueprint $table) {
            $table->string('youtube_url')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('academy_modules', function (Blueprint $table) {
            $table->dropColumn('youtube_url');
        });
    }
};
