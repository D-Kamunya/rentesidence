<?php

namespace App\Centresidence\Exceptions;

use RuntimeException;

/**
 * Thrown when an application cannot be submitted because it fails one or more
 * HARD underwriting rules (handbook §9.7 step 3).
 */
class UnderwritingFailedException extends RuntimeException
{
    public function __construct(public array $hardFailures, string $message = 'Application failed hard underwriting rules.')
    {
        parent::__construct($message);
    }
}
