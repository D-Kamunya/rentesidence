<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `finance_partners` — external lenders in the financing marketplace (handbook
 * §9.2.1). The 6th actor. A partner may optionally link to a `users` row for
 * portal login; API credentials are referenced, never stored in cleartext.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_partners', function (Blueprint $table) {
            $table->id();

            $table->string('company_name');
            $table->string('trading_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('tax_identifier')->nullable();

            $table->enum('status', ['active', 'inactive', 'suspended', 'onboarding'])->default('onboarding');

            $table->boolean('api_enabled')->default(false);
            $table->string('api_base_url')->nullable();
            $table->string('api_key_identifier')->nullable();

            $table->json('settlement_account_details')->nullable();
            $table->json('configuration_json')->nullable();

            // Optional linked portal login.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('onboarded_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_partners');
    }
};
