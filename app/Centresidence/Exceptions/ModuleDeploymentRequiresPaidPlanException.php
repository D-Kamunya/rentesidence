<?php

namespace App\Centresidence\Exceptions;

use RuntimeException;

/**
 * Thrown when a module deployment is attempted for an owner on the FREE pricing
 * model. Every deployed module carries a recurring monthly infrastructure cost
 * (platform software fee + LoRaWAN gateway usage). That cost needs a collection
 * rail — recovered from rent (transaction) or bundled in the plan invoice
 * (subscription). Free has neither, so the cost would be uncollectable. The
 * owner must move to a subscription or transaction plan before deploying.
 */
class ModuleDeploymentRequiresPaidPlanException extends RuntimeException
{
    public function __construct(public readonly int $ownerUserId)
    {
        parent::__construct(
            "Owner {$ownerUserId} is on a free plan; smart modules carry a monthly platform & gateway cost that a free plan cannot bill. Move to a subscription or transaction plan first."
        );
    }
}
