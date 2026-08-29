<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\FinancePartner;

/**
 * Resolves the two Centresidence earning rates for a finance partner, falling back
 * to the platform defaults in config('centresidence.partner_fees'). A partner is
 * "reviewed" simply by setting/clearing their own percentage on finance_partners.
 */
class PartnerFeeService
{
    /** One-time origination fee rate (%) — of facility principal. */
    public function originationRate(FinancePartner $partner): float
    {
        return (float) ($partner->origination_fee_percentage
            ?? config('centresidence.partner_fees.origination_percentage', 2.0));
    }

    /** Recurring servicing/collection fee rate (%) — of each remittance. */
    public function servicingRate(FinancePartner $partner): float
    {
        return (float) ($partner->servicing_fee_percentage
            ?? config('centresidence.partner_fees.servicing_percentage', 1.0));
    }

    /** Max share (%) of a single remittance that may go toward clearing origination. */
    public function originationCollectionCap(): float
    {
        return (float) config('centresidence.partner_fees.origination_collection_cap_percentage', 25.0);
    }
}
