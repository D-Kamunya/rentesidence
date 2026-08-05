<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired when a commission invoice is generated for a subscription-billed owner.
 */
class CommissionInvoiceGenerated
{
    use Dispatchable;

    public function __construct(public CentresidenceCommissionInvoice $invoice)
    {
    }
}
