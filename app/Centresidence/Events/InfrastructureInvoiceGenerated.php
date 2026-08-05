<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\OwnerInfrastructureInvoice;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired when a separate infrastructure invoice is generated (non-metered costs
 * for a transaction-billed owner).
 */
class InfrastructureInvoiceGenerated
{
    use Dispatchable;

    public function __construct(public OwnerInfrastructureInvoice $invoice)
    {
    }
}
