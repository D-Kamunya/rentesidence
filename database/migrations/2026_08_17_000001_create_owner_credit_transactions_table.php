<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unified prepaid-credit ledger. Replaces the two per-bucket ledgers
 * (sms_credit_transactions, agreement_credit_transactions) with ONE table keyed by
 * `bucket`, so the shared money rail (CreditService) has a single source of truth. Existing
 * rows are copied over here; the old tables are dropped in a follow-up migration once no
 * code references them.
 *
 * `type` and `status` are plain strings (not enums) so a new movement kind or bucket never
 * needs an ALTER … MODIFY migration on a shared host.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('owner_credit_transactions')) {
            Schema::create('owner_credit_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('bucket', 32);                       // sms | agreement | future
                $table->unsignedBigInteger('owner_user_id');
                $table->string('type', 24);                         // purchase|deduct|refund|manual_topup|package_grant
                $table->unsignedInteger('quantity');
                $table->decimal('amount_paid', 10, 2)->nullable();
                $table->unsignedInteger('balance_before');
                $table->unsignedInteger('balance_after');
                $table->string('reference')->nullable();            // STK CheckoutRequestID at purchase
                $table->string('payment_id')->nullable();
                $table->string('description')->nullable();
                $table->string('status', 12)->default('success');   // success|failed|pending
                $table->timestamps();

                $table->index(['owner_user_id', 'bucket']);
                $table->index(['bucket', 'status']);
            });
        }

        // Backfill from the legacy per-bucket ledgers (guarded — a fresh install has neither).
        $this->copyLegacy('sms_credit_transactions', 'sms');
        $this->copyLegacy('agreement_credit_transactions', 'agreement');
    }

    private function copyLegacy(string $sourceTable, string $bucket): void
    {
        if (! Schema::hasTable($sourceTable) || ! Schema::hasTable('owner_credit_transactions')) {
            return;
        }
        // Idempotent: don't double-copy if this migration is re-run after a partial failure.
        if (DB::table('owner_credit_transactions')->where('bucket', $bucket)->exists()) {
            return;
        }

        $hasPaymentId = Schema::hasColumn($sourceTable, 'payment_id');

        DB::table($sourceTable)->orderBy('id')->chunk(500, function ($rows) use ($bucket, $hasPaymentId) {
            $insert = [];
            foreach ($rows as $r) {
                $insert[] = [
                    'bucket'         => $bucket,
                    'owner_user_id'  => $r->owner_user_id,
                    'type'           => $r->type,
                    'quantity'       => $r->quantity,
                    'amount_paid'    => $r->amount_paid,
                    'balance_before' => $r->balance_before,
                    'balance_after'  => $r->balance_after,
                    'reference'      => $r->reference,
                    'payment_id'     => $hasPaymentId ? ($r->payment_id ?? null) : null,
                    'description'    => $r->description,
                    'status'         => $r->status,
                    'created_at'     => $r->created_at,
                    'updated_at'     => $r->updated_at,
                ];
            }
            if ($insert) {
                DB::table('owner_credit_transactions')->insert($insert);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_credit_transactions');
    }
};
