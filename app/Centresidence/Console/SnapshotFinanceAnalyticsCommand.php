<?php

namespace App\Centresidence\Console;

use App\Centresidence\Services\FinanceAnalyticsService;
use Illuminate\Console\Command;

/**
 * Takes the daily finance portfolio snapshot (handbook §9.9).
 *
 *   php artisan centresidence:snapshot-finance-analytics
 */
class SnapshotFinanceAnalyticsCommand extends Command
{
    protected $signature = 'centresidence:snapshot-finance-analytics';

    protected $description = 'Compute the daily Centresidence finance analytics snapshot';

    public function handle(FinanceAnalyticsService $service): int
    {
        if (! config('centresidence.enabled', true)) {
            $this->warn('Centresidence disabled; skipping analytics snapshot.');

            return self::SUCCESS;
        }

        $snapshot = $service->takeSnapshot();
        $this->info("Snapshot {$snapshot->snapshot_date->toDateString()}: "
            . "{$snapshot->total_active_facilities} active, "
            . "{$snapshot->facilities_in_default} defaulted, "
            . "collection rate {$snapshot->collection_rate}%.");

        return self::SUCCESS;
    }
}
