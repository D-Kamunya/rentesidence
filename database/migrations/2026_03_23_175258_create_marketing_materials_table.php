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
            Schema::create('marketing_materials', function (Blueprint $table) {
                $table->id();

                $table->string('title');
                $table->enum('type', ['pdf', 'text', 'link', 'png']);

                $table->text('content');

                $table->string('category');
                $table->integer('priority')->default(1);

                $table->integer('usage_count')->default(0);

                $table->boolean('is_active')->default(true);

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
        Schema::dropIfExists('marketing_materials');
    }
};
