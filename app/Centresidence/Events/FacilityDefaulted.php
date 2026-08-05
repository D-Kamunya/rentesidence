<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\FacilityDefault;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Default threshold reached (handbook event: FacilityDefaulted) — partner
 * notified, collections begins.
 */
class FacilityDefaulted
{
    use Dispatchable;

    public function __construct(public FacilityDefault $default)
    {
    }
}
