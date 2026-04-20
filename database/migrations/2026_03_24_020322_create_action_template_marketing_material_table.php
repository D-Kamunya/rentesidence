<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_template_marketing_material', function (Blueprint $table) {
            $table->id();

            // Foreign keys with shorthand
            $table->foreignId('action_template_id')
                  ->constrained('action_templates')
                  ->onDelete('cascade');

            $table->foreignId('marketing_material_id')
                  ->constrained('marketing_materials')
                  ->onDelete('cascade');

            $table->timestamps();

            // Prevent duplicates with a short index name
            $table->unique(
                ['action_template_id', 'marketing_material_id'],
                'atm_material_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_template_marketing_material');
    }
};
