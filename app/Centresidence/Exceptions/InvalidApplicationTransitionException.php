<?php

namespace App\Centresidence\Exceptions;

use RuntimeException;

/**
 * Thrown when an application status transition is not allowed by the lifecycle
 * state machine (handbook §9.3.1).
 */
class InvalidApplicationTransitionException extends RuntimeException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct("Invalid finance application transition: {$from} → {$to}.");
    }
}
