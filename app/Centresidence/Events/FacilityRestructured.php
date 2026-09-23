<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\FinanceFacility;
use Illuminate\Foundation\Events\Dispatchable;

/** New terms agreed after a default (handbook event: FacilityRestructured). */
class FacilityRestructured
{
    use Dispatchable;

    public function __construct(public FinanceFacility $facility)
    {
    }
}
