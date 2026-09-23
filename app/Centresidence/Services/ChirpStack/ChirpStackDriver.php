<?php

namespace App\Centresidence\Services\ChirpStack;

use App\Centresidence\Models\Device;
use App\Centresidence\Models\DeviceCommand;
use App\Centresidence\Models\Gateway;

/**
 * Abstraction over the physical LoRaWAN network (ChirpStack). The business
 * deployment flow (DeviceProvisioningService) creates the logical records;
 * the driver binds them to the network. Driver-gated by
 * `config('centresidence.chirpstack.driver')`, mirroring payouts/collections.
 */
interface ChirpStackDriver
{
    /**
     * Whether a freshly provisioned device should be activated immediately.
     * Simulated → true (no hardware to wait for); live → false (wait for the
     * ChirpStack join/uplink before flipping provisioning → active).
     */
    public function autoActivates(): bool;

    /** Register/ensure the gateway on the network. */
    public function registerGateway(Gateway $gateway): void;

    /** Register the device (DevEUI) on the network. */
    public function registerDevice(Device $device): void;

    /**
     * Send a queued downlink command to its device (e.g. credit_tokens). Returns
     * true if the network accepted it for delivery. Implementations update the
     * command status (queued → sent / failed); final delivery ack arrives later
     * via the uplink webhook (txack) where supported.
     */
    public function sendDownlink(DeviceCommand $command): bool;
}
