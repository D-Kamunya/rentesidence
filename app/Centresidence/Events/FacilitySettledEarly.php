<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\FinanceFacility;
use Illuminate\Foundation\Events\Dispatchable;

/** A facility was settled early (paid off ahead of schedule). */
class FacilitySettledEarly
{
    use Dispatchable;

    public function __construct(public FinanceFacility $facility)
    {
    }
}
