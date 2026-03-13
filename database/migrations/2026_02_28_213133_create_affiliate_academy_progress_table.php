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
    public function up()
    {
        Schema::create('affiliate_academy_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('academy_modules')->cascadeOnDelete();
            $table->integer('attempts')->default(0);
            $table->integer('score')->nullable();
            $table->boolean('needs_review')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['affiliate_id', 'module_id']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('affiliate_academy_progress');
    }
};
