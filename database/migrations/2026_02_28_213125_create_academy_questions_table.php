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
        Schema::create('academy_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('academy_modules')->cascadeOnDelete();
            $table->text('question');
            $table->integer('question_order')->default(1);
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('academy_questions');
    }
};
