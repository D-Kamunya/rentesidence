<?php

namespace App\Centresidence\Exceptions;

use RuntimeException;

/**
 * Thrown at application time when a facility's scheduled monthly repayment
 * cannot be collected within the owner's effective rent-deduction cap — i.e.
 * the facility could never repay on its agreed term, because the cap would
 * throttle collection below the schedule. The owner must accept a higher cap,
 * add a down-payment, or choose a longer term (within the partner's max).
 */
class FacilityInfeasibleException extends RuntimeException
{
    public function __construct(
        public readonly float $requiredPct,
        public readonly float $effectiveCapPct
    ) {
        parent::__construct('Facility repayment exceeds the owner\'s rent-deduction capacity.');
    }
}
