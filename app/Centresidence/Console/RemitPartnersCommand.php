<?php

namespace App\Centresidence\Console;

use App\Centresidence\Services\PartnerRemittanceService;
use Illuminate\Console\Command;

/**
 * Settles collected facility repayments out to finance partners on their
 * configured cadence (daily/direct or monthly on settlement_day). The actual
 * M-Pesa B2B payout happens in PartnerRemittanceService::markSent().
 *
 *   php artisan centresidence:remit-partners
 */
class RemitPartnersCommand extends Command
{
    protected $signature = 'centresidence:remit-partners';

    protected $description = 'Run due partner remittances (settle collected facility repayments to partners)';

    public function handle(PartnerRemittanceService $service): int
    {
        if (! config('centresidence.enabled', true)) {
            $this->warn('Centresidence disabled; skipping remittances.');

            return self::SUCCESS;
        }

        $batches = $service->runDueRemittances();
        $this->info(count($batches) . ' partner remittance batch(es) sent.');

        return self::SUCCESS;
    }
}
