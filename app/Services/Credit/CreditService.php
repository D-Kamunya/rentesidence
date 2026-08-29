<?php

namespace App\Services\Credit;

use App\Models\Owner;
use App\Models\OwnerCreditTransaction;
use Illuminate\Support\Facades\DB;

/**
 * THE prepaid-credit money rail. One atomic, ledgered, idempotent implementation shared by
 * every bucket (SMS, agreement, …) defined in config/credits.php. Bucket-specific extras
 * (SMS low-balance notifications, monthly package grant, retry lists) live in the thin
 * domain facades on top of this — SmsCreditsService / AgreementCreditsService — not here.
 *
 * Money invariants held here and nowhere else:
 *   • deductOne     — never lets a balance go negative; single locked decrement + ledger row.
 *   • addCredits    — internally idempotent on a pending transaction id: a re-fired M-Pesa
 *                     callback (or callback racing the verify fallback) credits exactly once.
 *   • pools         — a bucket may split its balance into an expiring "granted" allowance and
 *                     a non-expiring "purchased" pool; deducts draw granted-first so a renewal
 *                     reset never eats paid-for credits, and purchases always land in purchased.
 */
class CreditService
{
    /** Resolve + validate a bucket's config, or throw for an unknown key. */
    public static function config(string $bucket): array
    {
        $cfg = config("credits.buckets.$bucket");
        if (! is_array($cfg)) {
            throw new \InvalidArgumentException("Unknown credit bucket [$bucket].");
        }
        return $cfg;
    }

    public static function getOwner(?int $ownerUserId): ?Owner
    {
        return $ownerUserId ? Owner::where('user_id', $ownerUserId)->first() : null;
    }

    /** Current total balance for the bucket. */
    public static function balance(string $bucket, ?int $ownerUserId): int
    {
        $cfg   = self::config($bucket);
        $owner = self::getOwner($ownerUserId);
        return $owner ? (int) $owner->{$cfg['balance_column']} : 0;
    }

    /**
     * Balance split for display. For a pooled bucket: granted / purchased / total; for a
     * plain bucket the "purchased" pool is the whole balance.
     */
    public static function breakdown(string $bucket, ?int $ownerUserId): array
    {
        $cfg   = self::config($bucket);
        $owner = self::getOwner($ownerUserId);
        $total = $owner ? (int) $owner->{$cfg['balance_column']} : 0;

        if (empty($cfg['pools'])) {
            return ['granted' => 0, 'purchased' => $total, 'total' => $total];
        }

        return [
            'granted'   => $owner ? (int) $owner->{$cfg['granted_column']} : 0,
            'purchased' => $owner ? (int) $owner->{$cfg['purchased_column']} : 0,
            'total'     => $total,
        ];
    }

    /**
     * Atomically consume one credit. Returns false if the balance is empty. `$within`, if
     * given, runs inside the same locked transaction as fn(Owner $owner, int $before, int
     * $after) — used by the SMS facade to fire low/zero-balance notifications against a
     * consistent before/after.
     */
    public static function deductOne(string $bucket, int $ownerUserId, ?string $description = null, ?callable $within = null): bool
    {
        $cfg = self::config($bucket);

        return DB::transaction(function () use ($cfg, $bucket, $ownerUserId, $description, $within) {
            $owner = Owner::where('user_id', $ownerUserId)->lockForUpdate()->first();
            $balCol = $cfg['balance_column'];

            if (! $owner || (int) $owner->{$balCol} < 1) {
                return false;
            }

            $before = (int) $owner->{$balCol};

            // Pooled buckets draw the expiring granted allowance first, then purchased, so a
            // renewal that resets the grant never eats into credits the owner paid for.
            if (! empty($cfg['pools'])) {
                if ((int) $owner->{$cfg['granted_column']} > 0) {
                    $owner->decrement($cfg['granted_column']);
                } elseif ((int) $owner->{$cfg['purchased_column']} > 0) {
                    $owner->decrement($cfg['purchased_column']);
                }
            }
            $owner->decrement($balCol);
            $after = $before - 1;

            OwnerCreditTransaction::create([
                'bucket'         => $bucket,
                'owner_user_id'  => $ownerUserId,
                'type'           => 'deduct',
                'quantity'       => 1,
                'amount_paid'    => null,
                'balance_before' => $before,
                'balance_after'  => $after,
                'description'    => $description ?: ($cfg['deduct_description'] ?? 'Credit used'),
                'status'         => 'success',
            ]);

            if ($within) {
                $within($owner, $before, $after);
            }

            return true;
        });
    }

    /**
     * Add credits — from a confirmed STK top-up, manual admin top-up, or refund. Credits
     * always land in the purchased (non-expiring) pool for pooled buckets.
     *
     * Options: type (default 'purchase'), amount_paid, reference, payment_id, description,
     * existing_transaction_id.
     *
     * Idempotent on `existing_transaction_id`: only the pending→success transition credits
     * the balance. A re-fired callback finds status already 'success' → 0 rows affected → we
     * return the current balance WITHOUT crediting. Safe regardless of any caller-side guard.
     */
    public static function addCredits(string $bucket, int $ownerUserId, int $quantity, array $opts = []): int
    {
        $cfg = self::config($bucket);

        $type        = $opts['type']        ?? 'purchase';
        $amountPaid  = $opts['amount_paid']  ?? null;
        $reference   = $opts['reference']    ?? null;
        $paymentId   = $opts['payment_id']   ?? null;
        $description = $opts['description']   ?? ($cfg['label'] . ' purchase');
        $existingId  = $opts['existing_transaction_id'] ?? null;

        return DB::transaction(function () use ($cfg, $bucket, $ownerUserId, $quantity, $type, $amountPaid, $reference, $paymentId, $description, $existingId) {
            $owner  = Owner::where('user_id', $ownerUserId)->lockForUpdate()->first();
            if (! $owner) {
                return 0;
            }

            $balCol = $cfg['balance_column'];
            $before = (int) $owner->{$balCol};
            $after  = $before + $quantity;

            if ($existingId) {
                // Finalize the pending purchase row — but ONLY the pending→success transition
                // credits the balance (0 rows affected ⇒ already processed ⇒ no double credit).
                $update = [
                    'type'           => $type,
                    'quantity'       => $quantity,
                    'amount_paid'    => $amountPaid,
                    'balance_before' => $before,
                    'balance_after'  => $after,
                    'description'    => $description,
                    'status'         => 'success',
                ];
                // Only overwrite reference / payment_id when explicitly supplied, so we never
                // clobber the CheckoutRequestID stored at init with a null.
                if ($reference !== null) $update['reference']  = $reference;
                if ($paymentId !== null) $update['payment_id'] = $paymentId;

                $affected = OwnerCreditTransaction::where('id', $existingId)
                    ->where('bucket', $bucket)
                    ->where('status', 'pending')
                    ->update($update);

                if ($affected === 0) {
                    return $before; // already processed — no double credit
                }
            } else {
                OwnerCreditTransaction::create([
                    'bucket'         => $bucket,
                    'owner_user_id'  => $ownerUserId,
                    'type'           => $type,
                    'quantity'       => $quantity,
                    'amount_paid'    => $amountPaid,
                    'balance_before' => $before,
                    'balance_after'  => $after,
                    'reference'      => $reference,
                    'payment_id'     => $paymentId,
                    'description'    => $description,
                    'status'         => 'success',
                ]);
            }

            if (! empty($cfg['pools'])) {
                $owner->increment($cfg['purchased_column'], $quantity);
            }
            $owner->increment($balCol, $quantity);

            return $after;
        });
    }

    /**
     * Open a PENDING purchase row (for an STK top-up) and return it. The row is finalized by
     * addCredits() with its id once payment is server-confirmed.
     */
    public static function openPendingPurchase(string $bucket, int $ownerUserId, int $quantity, float $amountPaid, string $description): OwnerCreditTransaction
    {
        self::config($bucket); // validate
        $balance = self::balance($bucket, $ownerUserId);

        return OwnerCreditTransaction::create([
            'bucket'         => $bucket,
            'owner_user_id'  => $ownerUserId,
            'type'           => 'purchase',
            'quantity'       => $quantity,
            'amount_paid'    => $amountPaid,
            'balance_before' => $balance,
            'balance_after'  => $balance,
            'description'    => $description,
            'status'         => 'pending',
        ]);
    }

    /**
     * RESET a pooled bucket's granted (period) allowance — it does NOT roll over. Purchased
     * credits are untouched and the total is recomputed as granted + purchased. `$credits`
     * may be 0 to clear a leftover allowance. Only valid for pooled buckets.
     */
    public static function resetGrantedAllowance(string $bucket, int $ownerUserId, int $credits, string $description): void
    {
        $cfg = self::config($bucket);
        if (empty($cfg['pools'])) {
            throw new \LogicException("Bucket [$bucket] has no granted allowance to reset.");
        }
        $credits = max(0, $credits);

        DB::transaction(function () use ($cfg, $bucket, $ownerUserId, $credits, $description) {
            $owner = Owner::where('user_id', $ownerUserId)->lockForUpdate()->first();
            if (! $owner) {
                return;
            }

            $before    = (int) $owner->{$cfg['balance_column']};
            $purchased  = (int) $owner->{$cfg['purchased_column']};

            $owner->{$cfg['granted_column']} = $credits;               // reset — no rollover
            $owner->{$cfg['balance_column']} = $credits + $purchased;  // total = allowance + owned
            $owner->save();

            OwnerCreditTransaction::create([
                'bucket'         => $bucket,
                'owner_user_id'  => $ownerUserId,
                'type'           => 'package_grant',
                'quantity'       => $credits,
                'amount_paid'    => null,
                'balance_before' => $before,
                'balance_after'  => (int) $owner->{$cfg['balance_column']},
                'reference'      => null,
                'description'    => $description,
                'status'         => 'success',
            ]);
        });
    }

    /** KES unit price for one credit in this bucket (admin-set, with config fallback). */
    public static function pricePerUnit(string $bucket): float
    {
        $cfg = self::config($bucket);
        return (float) getOption($cfg['price_option'], $cfg['default_price']);
    }

    /** N credits → KES cost. */
    public static function amountForCredits(string $bucket, int $quantity): float
    {
        return round($quantity * self::pricePerUnit($bucket), 2);
    }

    /** KES amount → how many whole credits it buys. */
    public static function creditsForAmount(string $bucket, float $amount): int
    {
        $price = self::pricePerUnit($bucket);
        return $price > 0 ? (int) floor($amount / $price) : 0;
    }
}
