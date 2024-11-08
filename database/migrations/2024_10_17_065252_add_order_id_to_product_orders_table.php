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
        Schema::table('product_orders', function (Blueprint $table) {
            $table->string('order_id')->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('product_orders', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
    }
};
