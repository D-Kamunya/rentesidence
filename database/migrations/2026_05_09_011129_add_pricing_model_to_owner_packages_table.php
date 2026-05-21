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
        Schema::table('owner_packages', function (Blueprint $table) {
            $table->string('pricing_model')
                  ->default('subscription')
                  ->after('package_type'); 
        });
    }
    
    public function down()
    {
        Schema::table('owner_packages', function (Blueprint $table) {
            $table->dropColumn('pricing_model');
        });
    }
    
};
