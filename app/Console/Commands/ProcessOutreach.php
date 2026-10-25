<?php

namespace App\Console\Commands;

use App\Services\OutreachService;
use Illuminate\Console\Command;

/**
 * Scheduled: keep outreach campaigns moving without the admin lifting a finger — mark recipients
 * converted once they have an affiliate account, then send bounded follow-ups to non-responders.
 * This is the piece that fixes V1's "I forgot to follow up".
 */
class ProcessOutreach extends Command
{
    protected $signature = 'outreach:process';

    protected $description = 'Reconcile outreach conversions and send bounded follow-ups to non-responders.';

    public function handle(OutreachService $outreach): int
    {
        $converted = $outreach->reconcileConversions();
        $followed  = $outreach->processFollowUps();

        $this->info("Outreach: {$converted} newly converted, {$followed} follow-ups sent.");

        return self::SUCCESS;
    }
}
