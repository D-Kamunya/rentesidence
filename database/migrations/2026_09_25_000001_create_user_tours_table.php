<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records that a user has completed (or skipped) a one-time onboarding tour, keyed by a tour id
 * so more tours (owner, affiliate…) reuse the same rail. Shown once; the user can replay on demand
 * without un-recording it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_tours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('tour_key', 60);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tour_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_tours');
    }
};
