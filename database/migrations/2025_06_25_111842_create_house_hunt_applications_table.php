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
        Schema::create('house_hunt_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // null if not registered
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('contact_number',20);
            $table->string('job')->nullable();
            $table->integer('age')->nullable()->nullable();
            $table->integer('family_member')->nullable();
            $table->string('permanent_address');
            $table->string('permanent_country_id');
            $table->string('permanent_state_id');
            $table->string('permanent_city_id');
            $table->string('permanent_zip_code');
            $table->tinyInteger('status')->comment('1=accepted,2=pending,3=rejected')->default(HOUSE_HUNT_APPLICATION_PENDING);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('house_hunt_applications');
    }
};
