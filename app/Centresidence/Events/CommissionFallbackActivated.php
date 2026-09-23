<?php

namespace App\Centresidence\Events;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired when token-deduction fallback activates for an overdue commission
 * invoice (handbook event: SubscriptionInvoiceOverdue). Only the metered,
 * fallback-eligible portion is ever recovered this way.
 */
class CommissionFallbackActivated
{
    use Dispatchable;

    public function __construct(public CentresidenceCommissionInvoice $invoice)
    {
    }
}
