<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('objection_reason');
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->text('objection_reason')->nullable();
        });
    }
};
