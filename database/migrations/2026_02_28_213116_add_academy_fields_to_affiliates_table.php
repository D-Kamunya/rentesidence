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
        Schema::table('affiliates', function (Blueprint $table) {
            $table->enum('academy_status', ['not_started', 'in_progress', 'completed'])
                  ->default('not_started')
                  ->after('referral_code');
            $table->timestamp('academy_certified_at')
                  ->nullable()
                  ->after('academy_status'); 
        });
    }
    
    public function down()
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropColumn(['academy_status', 'academy_certified_at']);
        });
    }
};
