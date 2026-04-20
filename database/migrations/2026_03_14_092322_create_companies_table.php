<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {

            $table->id();

            $table->string('company_name');
            $table->string('normalized_name')->index();

            $table->string('country')->index();
            $table->string('city')->nullable()->index();

            $table->string('phone', 25)->nullable()->index();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            $table->integer('estimated_units')->nullable();

            $table->enum('property_type', [
                'apartment',
                'mixed_use',
                'commercial',
                'student_housing',
                'other'
            ])->nullable();

            $table->enum('sales_status', [
                'prospect',
                'contacted',
                'demo_done',
                'client',
                'not_interested'
            ])->default('prospect');

            $table->text('objection_reason')->nullable();

            $table->timestamps();

            $table->index(['normalized_name','city','country']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};