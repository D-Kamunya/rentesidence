<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\TokenPurchase;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired after a token purchase is processed (handbook event: TokenPurchased) —
 * device command dispatched, commission accounted, wallet credited, fallback
 * applied if active.
 */
class TokenPurchased
{
    use Dispatchable;

    public function __construct(public TokenPurchase $purchase)
    {
    }
}
