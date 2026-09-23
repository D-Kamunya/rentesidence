<?php

namespace App\Centresidence\Console;

use App\Centresidence\Services\CommissionFallbackService;
use App\Centresidence\Services\FacilityCollectionsService;
use Illuminate\Console\Command;

/**
 * Daily collections sweep: marks overdue facility schedules, accrues penalty, and
 * escalates facilities past their default threshold to defaulted; AND arms the
 * metered token-recovery fallback on overdue subscription-owner infra invoices
 * (B2 stage 5). The fallback only actually recovers once live token purchases
 * exist (C1) — this wiring makes it effective the moment they do.
 *
 *   php artisan centresidence:process-collections
 */
class ProcessCollectionsCommand extends Command
{
    protected $signature = 'centresidence:process-collections';

    protected $description = 'Mark overdue facility repayments, accrue penalties, escalate defaults, and arm the infra metered fallback';

    public function handle(FacilityCollectionsService $service, CommissionFallbackService $fallback): int
    {
        if (! config('centresidence.enabled', true)) {
            $this->warn('Centresidence disabled; skipping collections.');

            return self::SUCCESS;
        }

        $summary = $service->run();

        // Arm the metered token-recovery fallback on overdue module-infra invoices
        // (recovers ONLY the metered portion, from the owner's token revenue — never
        // the tenant's units). Inert until live tokens (C1); guarded so it can never
        // break the facility collections above.
        $armed = 0;
        try {
            $armed = $fallback->activateOverdue();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('process-collections: infra fallback activation failed — ' . $e->getMessage());
        }

        $this->info("Collections run: {$summary['overdue']} overdue, {$summary['defaulted']} newly defaulted; {$armed} infra fallback(s) armed.");

        return self::SUCCESS;
    }
}
