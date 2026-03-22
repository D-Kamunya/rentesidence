<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('affiliate_id')->constrained('users');

            $table->string('contact_person_name')->nullable();

            $table->enum('contact_person_role', [
                'owner',
                'property_manager',
                'caretaker',
                'unknown'
            ])->nullable();

            $table->enum('temperature', [
                'cold',
                'warm',
                'hot'
            ])->default('cold');

            $table->enum('status', [
                'active',
                'demo_scheduled',
                'demo_completed',
                'converted',
                'rejected',
                'expired',
                'lost'
            ])->default('active');

            $table->text('notes')->nullable();

            $table->timestamp('ownership_expires_at');

            $table->timestamp('last_activity_at')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
