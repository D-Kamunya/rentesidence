<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Demo account credentials + any scalar settings
        Schema::create('demo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('demo_login_url')->nullable();
            $table->string('demo_email')->nullable();
            $table->string('demo_password')->nullable();
            $table->text('demo_notes')->nullable(); // e.g. "Reset data after each session"
            $table->timestamps();
        });

        // Prep guide sections
        Schema::create('demo_prep_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_settings');
        Schema::dropIfExists('demo_prep_sections');
    }
};
