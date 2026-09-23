<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\FinanceFacility;
use Illuminate\Foundation\Events\Dispatchable;

/** A facility (and its repayment schedule) was created from an approved application. */
class FacilityCreated
{
    use Dispatchable;

    public function __construct(public FinanceFacility $facility)
    {
    }
}
