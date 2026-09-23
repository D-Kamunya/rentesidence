<?php

namespace App\Centresidence\Exceptions;

use RuntimeException;

/**
 * Thrown when assigning devices to a gateway would exceed its configured
 * `max_devices` capacity. Capacity is optional (null = unlimited).
 */
class GatewayCapacityExceededException extends RuntimeException
{
}
