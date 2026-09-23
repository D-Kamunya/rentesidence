<?php

namespace App\Centresidence\Services\ChirpStack;

use App\Centresidence\Models\Device;
use App\Centresidence\Models\DeviceCommand;
use App\Centresidence\Models\Gateway;
use Illuminate\Support\Carbon;

/**
 * No-network driver: devices/gateways are marked simulated and activate
 * immediately, so the metered billing → commission → fallback chain can be
 * feature-tested end to end without real LoRaWAN hardware. Default driver.
 */
class SimulatedChirpStackDriver implements ChirpStackDriver
{
    public function autoActivates(): bool
    {
        return true;
    }

    public function registerGateway(Gateway $gateway): void
    {
        // No network call. Record the simulated binding for transparency.
        $gateway->forceFill([
            'metadata' => array_merge($gateway->metadata ?? [], ['chirpstack' => 'simulated']),
        ])->save();
    }

    public function registerDevice(Device $device): void
    {
        $device->forceFill([
            'metadata' => array_merge($device->metadata ?? [], ['chirpstack' => 'simulated']),
        ])->save();
    }

    /** No network: mark the command delivered immediately. */
    public function sendDownlink(DeviceCommand $command): bool
    {
        $command->forceFill([
            'status'   => DeviceCommand::STATUS_ACKED,
            'response' => array_merge((array) $command->response, ['chirpstack' => 'simulated']),
            'acked_at' => Carbon::now(),
        ])->save();

        return true;
    }
}
