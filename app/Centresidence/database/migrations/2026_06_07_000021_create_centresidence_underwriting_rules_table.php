<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `underwriting_rules` — configurable rules-engine conditions per partner
 * product (handbook §9.2.3). Each rule compares an application/property
 * parameter against a threshold via an operator; hard rules auto-reject, soft
 * rules only warn. Rules are data, never hardcoded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('underwriting_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('finance_partner_module_id')->constrained('finance_partner_modules')->cascadeOnDelete();

            $table->string('rule_name');
            $table->enum('rule_type', ['boolean', 'threshold', 'range', 'ratio'])->default('threshold');

            // Parameter evaluated, e.g. occupancy_rate, net_cashflow, ratio key.
            $table->string('parameter');
            $table->enum('operator', ['gte', 'lte', 'eq', 'between', 'required']);

            // Threshold value(s) as string for flexibility ("70", "0.40", "3,5").
            $table->string('value')->nullable();

            $table->boolean('is_hard_rule')->default(true);
            $table->string('error_message')->nullable();

            $table->timestamps();

            $table->index('finance_partner_module_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('underwriting_rules');
    }
};
