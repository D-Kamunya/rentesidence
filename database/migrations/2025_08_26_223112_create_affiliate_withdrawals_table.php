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
        Schema::create('affiliate_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id')->nullable()->index();
            $table->float('amount')->default(0)->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=pending, 1=approved, 2=rejected')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('affiliate_id')
                  ->references('id')
                  ->on('affiliates')
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
        Schema::dropIfExists('affiliate_withdrawals');
    }
};
