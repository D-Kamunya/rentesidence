<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\DeviceCommand;
use App\Centresidence\Services\ChirpStack\ChirpStackDriver;

/**
 * Drains queued downlink commands (written by the Token Engine et al.) to the
 * network via the active ChirpStack driver. Under the simulated driver every
 * command is acked instantly; under the live driver each is enqueued on
 * ChirpStack for delivery. Scheduled every minute so token credits reach meters
 * promptly. Safe to re-run — only picks up commands that still need sending.
 */
class DeviceCommandDispatcher
{
    public function __construct(private ChirpStackDriver $driver)
    {
    }

    /**
     * @param  bool  $includeFailed  also retry previously-failed commands (ops-triggered).
     * @return array{processed:int, sent:int, failed:int}
     */
    public function dispatchQueued(int $limit = 100, bool $includeFailed = false): array
    {
        $statuses = $includeFailed
            ? [DeviceCommand::STATUS_QUEUED, DeviceCommand::STATUS_FAILED]
            : [DeviceCommand::STATUS_QUEUED];

        $commands = DeviceCommand::whereIn('status', $statuses)
            ->whereHas('device')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $sent = 0;
        $failed = 0;
        foreach ($commands as $command) {
            $this->driver->sendDownlink($command) ? $sent++ : $failed++;
        }

        return ['processed' => $commands->count(), 'sent' => $sent, 'failed' => $failed];
    }
}
