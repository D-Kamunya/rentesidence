<?php

namespace App\Centresidence\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Carbon;

/**
 * Fired when the monthly billing cycle begins (handbook event:
 * BillingCycleStart). Carries the billing month (first day).
 */
class BillingCycleStarted
{
    use Dispatchable;

    public function __construct(public Carbon $billingMonth)
    {
    }
}
