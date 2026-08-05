<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\FinanceFacility;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Funds released (handbook event: FacilityDisbursed) — platform fee is flagged
 * for Centresidence settlement and deployment is triggered downstream.
 */
class FacilityDisbursed
{
    use Dispatchable;

    public function __construct(public FinanceFacility $facility)
    {
    }
}
