<?php

namespace App\Centresidence\Services\ChirpStack\Codec;

use App\Centresidence\Models\DeviceCommand;

/**
 * The ONE device-specific seam. Everything else in the LoRaWAN integration is
 * meter-agnostic plumbing; only the byte-level payload format depends on the
 * physical meter. Swap in a meter-specific implementation via
 * config('centresidence.chirpstack.codec') once you have the datasheet — the
 * rest of the pipeline (webhook, dispatcher, driver, wallet) is unchanged.
 */
interface MeterCodec
{
    /**
     * Decode a raw uplink payload into the units CONSUMED since the previous
     * reading (a positive delta as a decimal string), or null if this uplink
     * carries no consumption (status/heartbeat frame). Implementations that get
     * cumulative meter totals must convert to a delta using $context, e.g. the
     * device's last reading.
     *
     * @param string $base64Payload Raw frame payload, base64 as ChirpStack sends it.
     * @param int    $fPort         LoRaWAN application port the frame arrived on.
     * @param array  $context       ['dev_eui'=>, 'device'=>Device, 'object'=>array|null] — ChirpStack's decoded `object` when a codec runs network-side.
     */
    public function decodeUplink(string $base64Payload, int $fPort, array $context = []): ?string;

    /**
     * Encode a queued device command (e.g. credit_tokens) into a downlink frame.
     *
     * @return array{f_port:int, data:string} `data` is base64 as ChirpStack expects.
     */
    public function encodeDownlink(DeviceCommand $command): array;
}
