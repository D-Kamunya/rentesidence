<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Binds an initiated M-Pesa STK push to its async callback so a settlement can only
 * be released against a push WE actually made. Recorded at push time (checkout id +
 * expected amount + the resource context), claimed once at callback time. Closes the
 * forgery where a crafted callback (public webhook) settled a resource that was never
 * paid for — used by the token-purchase and infra-bill collection flows (down-payment
 * and remittance bind via their own stored references).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('centresidence_stk_pending')) {
            return;
        }

        Schema::create('centresidence_stk_pending', function (Blueprint $table) {
            $table->id();
            $table->string('flow', 32);                       // 'token' | 'infra_bill'
            $table->string('checkout_request_id')->index();   // Safaricom CheckoutRequestID (issued at push)
            $table->json('context');                          // resource ids for the flow (module/tenant, owner…)
            $table->decimal('expected_amount', 14, 2)->nullable(); // the amount WE pushed (authoritative)
            $table->string('status', 16)->default('pending'); // 'pending' | 'consumed'
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            // One pending row per push; the callback claims by (flow, checkout id).
            $table->unique(['flow', 'checkout_request_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centresidence_stk_pending');
    }
};
