<?php

namespace App\Services\Agreement;

use App\Models\Owner;
use App\Services\Credit\CreditService;

/**
 * Owner's PURCHASED agreement-credit balance (rolls over). Thin domain facade over the
 * shared money rail (CreditService, bucket 'agreement') — kept so existing call sites read
 * naturally; all atomic/ledger/idempotency logic lives in CreditService. The free monthly
 * allowance is handled separately in AgreementService::sendEligibility.
 */
class AgreementCreditsService
{
    private const BUCKET = 'agreement';

    public static function getOwner(?int $ownerUserId): ?Owner
    {
        return CreditService::getOwner($ownerUserId);
    }

    /** Current purchased balance. */
    public static function balance(?int $ownerUserId): int
    {
        return CreditService::balance(self::BUCKET, $ownerUserId);
    }

    /** Atomically consume one purchased credit. Returns false if the balance is empty. */
    public static function deductOne(int $ownerUserId, string $description = ''): bool
    {
        return CreditService::deductOne(self::BUCKET, $ownerUserId, $description ?: 'Agreement sent');
    }

    /**
     * Add purchased credits (from a confirmed STK top-up). Idempotent on the given
     * transaction id — a re-fired callback credits once. Returns the new balance.
     */
    public static function addCredits(
        int $ownerUserId,
        int $quantity,
        float $amountPaid,
        ?string $reference = null,
        ?string $paymentId = null,
        string $description = 'Agreement credits purchase',
        ?int $existingTransactionId = null
    ): int {
        return CreditService::addCredits(self::BUCKET, $ownerUserId, $quantity, [
            'type'                    => 'purchase',
            'amount_paid'             => $amountPaid,
            'reference'               => $reference,
            'payment_id'              => $paymentId,
            'description'             => $description,
            'existing_transaction_id' => $existingTransactionId,
        ]);
    }
}
