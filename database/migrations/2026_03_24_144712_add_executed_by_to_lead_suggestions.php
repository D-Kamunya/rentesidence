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
        Schema::table('lead_suggestions', function (Blueprint $table) {
            $table->unsignedBigInteger('executed_by')->nullable()->after('executed_at');

            $table->foreign('executed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    
    public function down()
    {
        Schema::table('lead_suggestions', function (Blueprint $table) {
            $table->dropForeign(['executed_by']);
            $table->dropColumn('executed_by');
        });
    }
};
