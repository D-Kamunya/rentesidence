<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The "No vacancies right now → notify me" capture off the Vacancy Storefront empty
 * state (and the opt-in "House Watch" area-of-interest alert). Each row is a prospect we
 * can convert to a FREE standalone tenant account and/or ping when a matching vacancy
 * appears. This is the top of the vacancy-resolution growth funnel — a captured prospect
 * is a future tenant for the owner AND a standalone-tenant signup for the ecosystem.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vacancy_notify_requests')) {
            return;
        }

        Schema::create('vacancy_notify_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('contact_number', 30)->nullable();

            // Where the capture came from: a specific owner's storefront, a single property,
            // or the general house-hunt. Nullable scope ids keep it a plain area watch.
            $table->string('scope_type', 20)->default('general'); // owner | property | general
            $table->unsignedBigInteger('scope_owner_user_id')->nullable();
            $table->unsignedBigInteger('scope_property_id')->nullable();

            // House Watch — the prospect's area of interest (free text, matched loosely by city/state).
            $table->string('area_city', 120)->nullable();
            $table->string('area_state', 120)->nullable();

            // Lifecycle: did they create a free tenant account; when were they last alerted of a match.
            $table->unsignedBigInteger('converted_user_id')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('notified_at')->nullable();

            $table->timestamps();

            $table->index(['scope_type', 'scope_owner_user_id']);
            $table->index('scope_property_id');
            $table->index('notified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancy_notify_requests');
    }
};
