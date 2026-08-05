<?php

namespace App\Centresidence\Listeners;

use App\Centresidence\Events\FacilityDisbursed;
use App\Centresidence\Services\DownPaymentCollectionService;

/**
 * On FacilityDisbursed, collect the owner's down-payment (if any) toward the
 * deployment cost. Centresidence is the installer/payee, so the contribution is
 * collected to Centresidence. Driver-gated inside the service; no-op when the
 * facility carries no owner contribution.
 */
class CollectDownPaymentOnDisbursement
{
    public function __construct(private DownPaymentCollectionService $collections)
    {
    }

    public function handle(FacilityDisbursed $event): void
    {
        $this->collections->collect($event->facility);
    }
}
