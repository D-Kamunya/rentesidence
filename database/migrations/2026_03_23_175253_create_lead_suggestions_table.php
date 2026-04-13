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
        Schema::create('lead_suggestions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('affiliate_id');

            $table->string('message');

            $table->enum('action_type', ['whatsapp', 'email', 'call']);
            $table->string('category'); // intro, follow_up, trial, reengage

            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->enum('status', ['pending', 'completed', 'dismissed'])->default('pending');

            // tracking
            $table->timestamp('executed_at')->nullable();
            $table->string('execution_type')->nullable();

            // expiry
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index(['lead_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_suggestions');
    }
};
