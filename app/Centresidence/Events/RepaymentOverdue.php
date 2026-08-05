<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\FinanceFacility;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Grace period expired on a facility (handbook event: RepaymentOverdue) —
 * penalty accrual begins and notifications escalate.
 */
class RepaymentOverdue
{
    use Dispatchable;

    public function __construct(public FinanceFacility $facility, public int $daysPastDue)
    {
    }
}
