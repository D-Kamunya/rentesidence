<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\FinanceFacility;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * The owner's down-payment for a partially-financed facility has been collected
 * by Centresidence (the installer/payee) — the deployment cost is now fully
 * settled (partner-financed portion + owner contribution).
 */
class DownPaymentCollected
{
    use Dispatchable;

    public function __construct(public FinanceFacility $facility)
    {
    }
}
