<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('marketing_materials', function (Blueprint $table) {
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table('marketing_materials', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_name']);
        });
    }
};
