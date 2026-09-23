<?php

namespace App\Centresidence\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired after a rent collection has been run through the Deduction Engine
 * (handbook event: RentCollected). Carries the owner, the gross rent, the total
 * Centresidence deducted, and the source rent transaction id.
 */
class RentCollected
{
    use Dispatchable;

    public function __construct(
        public int $ownerId,
        public string $grossRent,
        public string $totalDeducted,
        public ?int $rentTransactionId = null
    ) {
    }
}
