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
        Schema::table('lead_activities', function (Blueprint $table) {
        $table->unsignedBigInteger('suggestion_id')->nullable()->after('lead_id');
        $table->foreign('suggestion_id')->references('id')->on('lead_suggestions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_activities', function (Blueprint $table) {
            $table->dropForeign(['suggestion_id']); 
            $table->dropColumn('suggestion_id');   
        });
    }
};
