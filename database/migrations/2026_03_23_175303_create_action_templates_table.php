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
        Schema::create('action_templates', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->enum('action_type', ['whatsapp', 'email', 'call']);
            $table->string('category');

            $table->text('message_template')->nullable();

            $table->json('material_ids')->nullable();

            $table->boolean('is_default')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('action_templates');
    }
};
