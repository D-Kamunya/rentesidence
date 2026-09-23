<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Presentation/education fields on modules, so the owner financing experience
 * can TEACH owners what a module is and how it improves their cashflow — not
 * just list prices. Drives the marketplace-style module cards and detail pages.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('tagline')->nullable()->after('name');
            $table->text('cashflow_benefit')->nullable()->after('description');
            $table->text('how_it_works')->nullable()->after('cashflow_benefit');
            $table->json('benefits')->nullable()->after('how_it_works');
            $table->string('icon')->nullable()->after('benefits');         // ri-* class
            $table->string('accent_color')->nullable()->after('icon');     // hex
            $table->string('image_url')->nullable()->after('accent_color');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'cashflow_benefit', 'how_it_works', 'benefits', 'icon', 'accent_color', 'image_url']);
        });
    }
};
