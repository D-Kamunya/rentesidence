<?php

namespace App\Centresidence\Console;

use App\Centresidence\Services\DeviceCommandDispatcher;
use Illuminate\Console\Command;

/**
 * Sends queued device downlinks (e.g. credit_tokens) to the LoRaWAN network.
 * Scheduled every minute; `--include-failed` retries prior failures.
 */
class DispatchDeviceCommandsCommand extends Command
{
    protected $signature = 'centresidence:dispatch-device-commands {--limit=100} {--include-failed}';

    protected $description = 'Dispatch queued Centresidence device downlink commands to the LoRaWAN network';

    public function handle(DeviceCommandDispatcher $dispatcher): int
    {
        if (! config('centresidence.enabled', true)) {
            $this->warn('Centresidence disabled — skipping.');
            return self::SUCCESS;
        }

        $result = $dispatcher->dispatchQueued((int) $this->option('limit'), (bool) $this->option('include-failed'));
        $this->info("Device commands — processed {$result['processed']}, sent {$result['sent']}, failed {$result['failed']}.");

        return self::SUCCESS;
    }
}
