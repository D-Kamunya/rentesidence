<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired when the metered fallback balance reaches zero (handbook event:
 * TokenDeductionClearsMeteredBalance). Fallback deactivates; any non-metered
 * balance remains on the invoice via standard dunning.
 */
class CommissionFallbackCleared
{
    use Dispatchable;

    public function __construct(public CentresidenceCommissionInvoice $invoice)
    {
    }
}
