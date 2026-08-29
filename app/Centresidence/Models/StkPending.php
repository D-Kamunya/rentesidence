<?php

namespace App\Centresidence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * A pending M-Pesa STK push we initiated, awaiting its async callback. Recording one
 * at push time and claiming it once at callback time is what makes the public callback
 * endpoints unforgeable: a crafted callback with no matching pending row settles nothing,
 * and the amount credited comes from what WE pushed (`expected_amount`), never the payload.
 *
 * @property string      $flow
 * @property string      $checkout_request_id
 * @property array       $context
 * @property float|null  $expected_amount
 * @property string      $status
 */
class StkPending extends Model
{
    protected $table = 'centresidence_stk_pending';

    protected $fillable = [
        'flow', 'checkout_request_id', 'context', 'expected_amount', 'status', 'consumed_at',
    ];

    protected $casts = [
        'context'         => 'array',
        'expected_amount' => 'decimal:2',
        'consumed_at'     => 'datetime',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_CONSUMED = 'consumed';

    /**
     * Record a push we just fired successfully. No-op without a checkout id (nothing to
     * bind against). `firstOrCreate` on (flow, checkout_request_id) is idempotent should
     * the same push somehow be recorded twice.
     */
    public static function record(string $flow, ?string $checkoutRequestId, array $context, ?float $expectedAmount = null): void
    {
        if (empty($checkoutRequestId)) {
            return;
        }

        static::firstOrCreate(
            ['flow' => $flow, 'checkout_request_id' => $checkoutRequestId],
            [
                'context'         => $context,
                'expected_amount' => $expectedAmount,
                'status'          => self::STATUS_PENDING,
            ]
        );
    }

    /**
     * Atomically claim a pending push at callback time. Returns the row (with the
     * authoritative `expected_amount`) only when a PENDING row matches the flow, the
     * callback's checkout id AND the resource context from the callback URL — and marks
     * it consumed in the same transaction so a re-fired/duplicate callback can't settle
     * twice. Returns null for a forged/unknown/already-consumed callback → caller settles
     * nothing.
     */
    public static function claim(string $flow, ?string $checkoutRequestId, array $context): ?self
    {
        if (empty($checkoutRequestId)) {
            return null;
        }

        return DB::transaction(function () use ($flow, $checkoutRequestId, $context) {
            $row = static::query()
                ->where('flow', $flow)
                ->where('checkout_request_id', $checkoutRequestId)
                ->where('status', self::STATUS_PENDING)
                ->lockForUpdate()
                ->first();

            if (! $row) {
                return null;
            }

            // Belt-and-suspenders: the context recorded at push must match the ids the
            // callback URL carries (a genuine callback always echoes the same resource).
            // Loose, order-independent compare — checkout_request_id is already globally
            // unique, so this is a secondary guard, not the primary authenticity check.
            if ((array) $row->context != $context) {
                return null;
            }

            $row->update([
                'status'      => self::STATUS_CONSUMED,
                'consumed_at' => now(),
            ]);

            return $row;
        });
    }
}
