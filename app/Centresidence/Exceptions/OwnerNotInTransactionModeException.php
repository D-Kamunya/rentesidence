<?php

namespace App\Centresidence\Exceptions;

use RuntimeException;

/**
 * Thrown when an owner attempts to begin a financing application without being
 * on the transaction pricing model. Financing requires transaction mode so that
 * rent flows through the company account and facility repayments can be deducted
 * at source.
 */
class OwnerNotInTransactionModeException extends RuntimeException
{
    public function __construct(int $ownerUserId)
    {
        parent::__construct(
            "Owner {$ownerUserId} must switch to the transaction pricing model before applying for financing."
        );
    }
}
