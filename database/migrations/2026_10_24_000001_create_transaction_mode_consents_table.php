<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Durable legal-evidence log of an owner consenting to the Transaction-mode money-path change —
 * i.e. that their rent will be collected into their Centresidence wallet (taken at source) rather
 * than landing directly with them. Recorded both when an owner self-switches to Transaction and
 * when they apply for financing (which moves them to Transaction on disbursement). One row per
 * consent event, with the exact statement they agreed to + request fingerprint.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_mode_consents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id')->index();
            $table->string('source', 40);                 // self_switch | financing_apply
            $table->string('consent_version', 20)->default('1.0');
            $table->text('acknowledged_text')->nullable(); // the exact statement shown + agreed to
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('consented_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_mode_consents');
    }
};
