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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('rent_payment_rating')->nullable()->after('close_reason');
            $table->string('discipline_rating')->nullable()->after('rent_payment_rating');
            $table->text('closing_remarks')->nullable()->after('discipline_rating');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['rent_payment_rating', 'discipline_rating', 'closing_remarks']);
        });
    }
};
