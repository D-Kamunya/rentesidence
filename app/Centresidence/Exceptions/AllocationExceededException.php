<?php

namespace App\Centresidence\Exceptions;

use RuntimeException;

/**
 * Thrown when an infrastructure_topology allocation would push an asset's total
 * allocation over 100% (handbook §4.2 invariant).
 */
class AllocationExceededException extends RuntimeException
{
}
