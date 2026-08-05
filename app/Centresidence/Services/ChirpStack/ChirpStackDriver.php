<?php

namespace App\Centresidence\Services\ChirpStack;

use App\Centresidence\Models\Device;
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
}
