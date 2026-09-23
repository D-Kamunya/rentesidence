<?php

namespace App\Centresidence\Exceptions;

use RuntimeException;

/**
 * Thrown when an owner tries to leave transaction mode while they still have an
 * active financing facility. The mode is locked to transaction until all
 * facilities are completed, so repayments keep flowing.
 */
class FacilityActiveModeLockException extends RuntimeException
{
    public function __construct(int $ownerUserId)
    {
        parent::__construct(
            "Owner {$ownerUserId} cannot leave transaction mode while a financing facility is active."
        );
    }
}
